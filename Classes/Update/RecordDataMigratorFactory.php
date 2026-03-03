<?php

declare(strict_types=1);


namespace WEBcoast\Migrator\Update;


use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class RecordDataMigratorFactory
{
    public function __construct(
        #[Autowire(service: 'webcoast.migrator.record_data_migrator_collection')]
        protected RecordDataMigratorCollection $migratorCollection
    ) {}

    /**
     * @return string[]
     */
    public function getSupportedContentTypes(): array
    {
        return array_map(fn ($recordDataMigratorMapping) => $recordDataMigratorMapping->getContentTypeIdentifier(), $this->migratorCollection->getAll());
    }

    public function getMigratorMapping(string $contentType): RecordDataMigratorMapping
    {
        if (!($this->migratorCollection->get($contentType) ?? null)) {
            throw new \RuntimeException(sprintf('No migrator mapping found for content type "%s".', $contentType));
        }

        return $this->migratorCollection->get($contentType);
    }

    public function getMigrator(string $contentType): RecordDataMigrator
    {
        if (!($this->migratorCollection->get($contentType) ?? null)) {
            throw new \RuntimeException(sprintf('No migrator found for content type "%s".', $contentType));
        }

        return GeneralUtility::makeInstance($this->migratorCollection->get($contentType)->getMigratorClass());
    }
}
