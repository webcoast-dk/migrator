<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Migration;

class Section extends Field implements \Iterator
{
    public function __construct(string $identifier, protected string $objectIdentifier, string $label, ?string $description = null, protected ?FieldCollection $fields = null)
    {
        parent::__construct($identifier, FieldType::SECTION, $label, $description);
    }

    public function getObjectIdentifier(): string
    {
        return $this->objectIdentifier;
    }

    public function getFields(): ?FieldCollection
    {
        return $this->fields;
    }

    public function current(): object
    {
        return $this->fields->current();
    }

    public function next(): void
    {
        $this->fields->next();
    }

    public function key(): mixed
    {
        return $this->fields->key();
    }

    public function valid(): bool
    {
        return $this->fields->valid();
    }

    public function rewind(): void
    {
        $this->fields->rewind();
    }
}
