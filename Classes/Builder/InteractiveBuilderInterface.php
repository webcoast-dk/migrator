<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Builder;

use Symfony\Component\Console\Style\SymfonyStyle;

interface InteractiveBuilderInterface
{
    public function setIO(SymfonyStyle $io);
}
