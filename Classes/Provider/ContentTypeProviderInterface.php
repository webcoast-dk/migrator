<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Provider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use WEBcoast\Migrator\Migration\ContentType;

#[AutoconfigureTag('webcoast.migrator.content_type_provider')]
interface ContentTypeProviderInterface
{
    /**
     * Returns an identifier for the content type provider.
     */
    public function getIdentifier(): string;

    /**
     * Returns a description for the content type provider.
     */
    public function getDescription(): string;

    /**
     * Returns a list of available content types provided by this provider.
     *
     * @return string[][]
     */
    public function getAvailableContentTypes(): iterable;

    /**
     * Returns the configuration for a given content type.
     */
    public function getConfiguration(string $contentType): ContentType;

    public function getFrontendTemplate(string $contentType): ?string;

    public function getBackendPreviewTemplate(string $contentType): ?string;

    public function getIcon(string $contentType): ?string;

    public function getRecordData(array $rawRecord): array;
}
