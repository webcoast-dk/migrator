<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Provider;

interface ContainerTemplateProviderInterface
{
    public function getContainerTemplate(string $contentType): ?string;
}
