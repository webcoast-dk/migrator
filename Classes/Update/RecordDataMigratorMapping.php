<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Update;

class RecordDataMigratorMapping
{
    public function __construct(
        protected string $providerIdentifier,
        protected string $contentTypeIdentifier,
        protected string $migratorClass
    ) {}

    public function getProviderIdentifier(): string
    {
        return $this->providerIdentifier;
    }

    public function getContentTypeIdentifier(): string
    {
        return $this->contentTypeIdentifier;
    }

    public function getMigratorClass(): string
    {
        return $this->migratorClass;
    }
}
