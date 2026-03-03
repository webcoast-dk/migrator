<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Service;

use Doctrine\DBAL\Result;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Provider\ContentTypeProviderCollection;
use WEBcoast\Migrator\Update\NewIdMappingAwareInterface;
use WEBcoast\Migrator\Update\RecordDataMigratorFactory;

class UpgradeService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected Connection $connection;

    public function __construct(
        ConnectionPool $connectionPool,
        protected RecordDataMigratorFactory $recordDataMigratorFactory,
        protected ContentTypeProviderCollection $contentTypeProviders,
        protected TcaSchemaFactory $tcaSchemaFactory,
        protected LanguageServiceFactory $languageServiceFactory
    ) {
        $this->connection = $connectionPool->getConnectionForTable('tt_content');
    }

    public function migrateContentElements(Result $result): void
    {
        while ($record = $result->fetchAssociative()) {
            $recordMigratorMapping = $this->recordDataMigratorFactory->getMigratorMapping($record['CType']);
            $provider = $this->contentTypeProviders->getProvider($recordMigratorMapping->getProviderIdentifier());
            $recordDataMigrator = $this->recordDataMigratorFactory->getMigrator($record['CType']);

            $data = $provider->getRecordData($record);

            $dataMap = [
                'tt_content' => [
                    $record['uid'] => $recordDataMigrator->migrate($data, $record),
                ],
            ];

            // Make sure, the CType is set
            if (!$dataMap['tt_content'][$record['uid']]['CType'] ?? null) {
                throw new \UnexpectedValueException(sprintf('The data for record with uid %s migrated by %s does not contain a CType value', $record['uid'], get_class($recordDataMigrator)));
            }

            $referencedTableData = $recordDataMigrator->getReferencedTableData();
            foreach ($referencedTableData as $tableName => &$records) {
                $schema = $this->tcaSchemaFactory->get($tableName);
                foreach ($records as &$recordData) {
                    if (!($recordData['pid'] ?? null)) {
                        $recordData['pid'] = $record['pid'];
                    }

                    if ($schema->isLanguageAware() && !($recordData[$schema->getCapability(TcaSchemaCapability::Language)->getLanguageField()->getName()] ?? null)) {
                        $recordData[$schema->getCapability(TcaSchemaCapability::Language)->getLanguageField()->getName()] = $record['sys_language_uid'];
                    }
                }
            }
            $dataMap = array_replace_recursive($referencedTableData, $dataMap);

            if (!Environment::isCli()) {
                Bootstrap::initializeBackendUser(BackendUserAuthentication::class, ServerRequestFactory::fromGlobals());
                Bootstrap::initializeBackendAuthentication();
                $GLOBALS['LANG'] = $this->languageServiceFactory->createFromUserPreferences($GLOBALS['BE_USER']);
            }

            // Update the CType beforehand, because some data handling logic relies on the new CType
            $this->connection->update(
                'tt_content',
                [
                    'CType' => $dataMap['tt_content'][$record['uid']]['CType'],
                ],
                [
                    'uid' => $record['uid'],
                ]
            );

            /** @var DataHandler $dataHandler */
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->bypassWorkspaceRestrictions = true;
            $dataHandler->start($dataMap, $recordDataMigrator->getCommandMap());
            $dataHandler->process_datamap();
            $dataHandler->process_cmdmap();

            if ($recordDataMigrator instanceof NewIdMappingAwareInterface) {
                $recordDataMigrator->setNewIdMappings($dataHandler->substNEWwithIDs);
            }
        }
    }
}
