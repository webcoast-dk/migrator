<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Update;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordDataMigratorCollection
{
    public function __construct(protected array $mapping) {
        $this->mapping = array_map(function ($item) {
            return new RecordDataMigratorMapping(...GeneralUtility::trimExplode(':', $item, true));
        }, $this->mapping);
    }

    /**
     * @return array|RecordDataMigratorMapping[]
     */
    public function getAll()
    {
        return $this->mapping;
    }

    public function get(string $contentType): ?RecordDataMigratorMapping
    {
        return $this->mapping[$contentType] ?? null;
    }
}
