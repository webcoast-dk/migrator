<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Migration;

class Field
{
    protected ?string $dbField = null;

    public function __construct(protected string $identifier, protected ?FieldType $fieldType, protected string $label, protected ?string $description = null, protected ?bool $exclude = null, protected array|string|null $displayCondition = null, protected ?array $configuration = null)
    {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getType(): ?FieldType
    {
        return $this->fieldType;
    }

    public function setType(?FieldType $fieldType): void
    {
        $this->fieldType = $fieldType;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getExclude(): ?bool
    {
        return $this->exclude;
    }

    public function getDisplayCondition(): array|string|null
    {
        return $this->displayCondition;
    }

    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    public function setConfiguration(?array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getDbField(): ?string
    {
        return $this->dbField;
    }

    public function setDbField(?string $dbField): void
    {
        $this->dbField = $dbField;
    }

    public function isTab(): bool
    {
        return $this->fieldType === FieldType::TAB;
    }

    public function isSection(): bool
    {
        return $this->fieldType === FieldType::SECTION;
    }
}
