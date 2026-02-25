<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Builder;

use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractInteractiveContentTypeBuilder implements ContentTypeBuilderInterface, InteractiveBuilderInterface
{
    protected SymfonyStyle $io;

    public function setIO(SymfonyStyle $io): void
    {
        $this->io = $io;
    }
}
