<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Builder;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use WEBcoast\Migrator\Provider\ContentTypeProviderInterface;

#[AutoconfigureTag('webcoast.migrator.content_type_builder')]
interface ContentTypeBuilderInterface
{
    public function getTitle(): string;
    public function buildContentTypeConfiguration(string $contentTypeName, array $contentTypeConfiguration, ContentTypeProviderInterface $contentTypeProvider): void;

    public function supports(array $normalizedConfiguration): bool;
}
