<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Migration;

class Tab extends Field
{
    public function __construct(string $identifier, string $label, ?string $description = null, ?array $configuration = null)
    {
        parent::__construct($identifier, FieldType::TAB, $label, $description, $configuration);
    }
}
