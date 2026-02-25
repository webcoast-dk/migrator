<?php

declare(strict_types=1);


namespace WEBcoast\Migrator\Update;


use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordDataMigratorFactory
{
    public function __construct(
        #[Autowire(service: 'webcoast.migrator.record_data_migrator_collection')]
        protected readonly RecordDataMigratorCollection $migratorCollection
    ) {}

    public function getSupportedContentTypes(): array
    {
        return array_keys($this->migratorCollection->getAll());
    }

    public function getMigrator(string $contentType): RecordDataMigrator
    {
        if (!($this->migratorCollection->get($contentType) ?? null)) {
            throw new \RuntimeException(sprintf('No migrator class found for content type "%s".', $contentType));
        }

        return GeneralUtility::makeInstance($this->migratorCollection->get($contentType));
    }
}
