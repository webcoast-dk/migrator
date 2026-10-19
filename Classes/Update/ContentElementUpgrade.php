<?php

declare(strict_types=1);


namespace WEBcoast\Migrator\Update;


use Doctrine\DBAL\ArrayParameterType;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use WEBcoast\Migrator\Service\UpgradeService;

#[UpgradeWizard('migrator-content-element-upgrade')]
readonly class ContentElementUpgrade implements UpgradeWizardInterface, RepeatableInterface, ChattyInterface
{
    protected OutputInterface $output;

    public function __construct(protected RecordDataMigratorFactory $recordDataMigratorFactory, protected UpgradeService $upgradeService) {}

    public function getTitle(): string
    {
        return 'Migrator: Record migration';
    }

    public function getDescription(): string
    {
        return 'Migrates all content elements with the help of registered RecordDataMigrators.';
    }

    public function executeUpdate(): bool
    {
        foreach ($this->recordDataMigratorFactory->getSupportedContentTypes() as $contentType) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

            $queryBuilder
                ->select('c.*')
                ->from('tt_content', 'c')
                ->join('c', 'pages', 'p', 'c.pid = p.uid')
                ->where(
                    $queryBuilder->expr()->eq('c.CType', $queryBuilder->createNamedParameter($contentType))
                );

            // Make sure, we process element per page, language and colPos
            $queryBuilder
                ->orderBy('c.pid')
                ->addOrderBy('c.sys_language_uid')
                ->addOrderBy('c.colPos')
                ->addOrderBy('c.sorting');

            $result = $queryBuilder->executeQuery();

            if ($result->rowCount() > 0) {
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE && $this->output instanceof SymfonyStyle) {
                    $this->output->section(sprintf('Migrating %d content elements of type %s', $result->rowCount(), $contentType));
                } elseif ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->writeln(sprintf('Migrating %d content elements of type %s', $result->rowCount(), $contentType));
                }

                $this->upgradeService->migrateContentElements($result, $this->output);
            }

        }

        return true;
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $queryBuilder
            ->count('c.uid')
            ->from('tt_content', 'c')
            ->join('c', 'pages', 'p', 'c.pid = p.uid')
            ->where(
                $queryBuilder->expr()->in('c.CType', $queryBuilder->createNamedParameter($this->recordDataMigratorFactory->getSupportedContentTypes(), ArrayParameterType::STRING))
            );

        return $queryBuilder->executeQuery()->fetchOne() > 0;
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
}
