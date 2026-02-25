<?php

declare(strict_types=1);


namespace WEBcoast\Migrator\Update;


interface NewIdMappingAwareInterface
{
    public function setNewIdMappings(array $newIdMappings): void;
}
