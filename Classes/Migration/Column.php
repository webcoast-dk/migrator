<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Migration;

class Column
{
    public function __construct(protected string $name, protected int $colPos, protected ?int $rowspan = null, protected ?int $colspan = null, protected ?array $allowed = null, protected ?array $disallowed = null, protected ?int $maxitems = null)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getColPos(): int
    {
        return $this->colPos;
    }

    public function getRowspan(): ?int
    {
        return $this->rowspan;
    }

    public function getColspan(): ?int
    {
        return $this->colspan;
    }

    public function getAllowed(): ?array
    {
        return $this->allowed;
    }

    public function getDisallowed(): ?array
    {
        return $this->disallowed;
    }

    public function getMaxitems(): ?int
    {
        return $this->maxitems;
    }
}
