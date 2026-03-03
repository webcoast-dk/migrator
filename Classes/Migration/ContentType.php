<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Migration;

class ContentType
{
    public function __construct(protected string $identifier, protected string $title, protected string $description, protected ?FieldCollection $fields = null, protected ?Grid $grid = null, protected ?string $iconIdentifier = null, protected ?string $group = null)
    {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return \iterable<Field>|null
     */
    public function getFields(): ?FieldCollection
    {
        return $this->fields;
    }

    public function getGrid(): ?Grid
    {
        return $this->grid;
    }

    public function getIconIdentifier(): ?string
    {
        return $this->iconIdentifier;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }
}
