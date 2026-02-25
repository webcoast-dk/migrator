<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Service;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use TYPO3\CMS\ContentBlocks\Builder\ContentBlockBuilder;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use TYPO3\CMS\ContentBlocks\Service\PackageResolver;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\Migrator\Builder\ContentTypeBuilderInterface;
use WEBcoast\Migrator\Builder\InteractiveBuilderInterface;
use WEBcoast\Migrator\Configuration\ContentTypeProviderCollection;

readonly class MigrationService
{
    protected SymfonyStyle $io;

    protected string $targetExtensionKey;

    /**
     * @param PackageResolver $packageResolver
     * @param FlexFormService $flexFormService
     * @param ContentBlockRegistry $contentBlockRegistry
     * @param ContentBlockBuilder $contentBlockBuilder
     * @param ContentTypeProviderCollection $contentTypeProviders
     * @param iterable|ContentTypeBuilderInterface[] $contentTypeBuilders
     */
    public function __construct(
        protected PackageResolver $packageResolver,
        protected FlexFormService $flexFormService,
        protected ContentBlockRegistry $contentBlockRegistry,
        protected ContentBlockBuilder $contentBlockBuilder,
        protected ContentTypeProviderCollection $contentTypeProviders,
        #[AutowireIterator(tag: 'webcoast.migrator.content_type_builder')]
        protected iterable $contentTypeBuilders
    ) {}

    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;
    }

    public function migrate(string $combinedContentTypeIdentifier): void
    {
        [$providerIdentifier, $contentTypeIdentifier] = GeneralUtility::trimExplode(':', $combinedContentTypeIdentifier, true);
        $provider = $this->contentTypeProviders->getProvider($providerIdentifier);
        $contentTypeConfiguration = $provider->getConfiguration($contentTypeIdentifier);

        if (iterator_count($this->contentTypeBuilders) === 0) {
            throw new \RuntimeException('No content type builders are registered. Please register at least one content type builder to proceed with the migration.');
        }

        // Check all registered content type builders if they support the content type and ask the user if they want to use the builder for the migration. If the user agrees, let the builder build the content type configuration.
        // It is possible to have multiple builders supporting the same content type. In this case, the user can choose which builder to use for the migration, or even use multiple builders for the same content type. This allows to combine different builders, for example a content block builder and a custom builder that adds additional configuration to the content block.
        foreach ($this->contentTypeBuilders as $contentTypeBuilder) {
            if ($contentTypeBuilder->supports($contentTypeConfiguration)) {
                if ($this->io->askQuestion(new ConfirmationQuestion(sprintf('Do you want to use the "%s" builder for this content type?', $contentTypeBuilder->getTitle()), true))) {
                    if ($contentTypeBuilder instanceof InteractiveBuilderInterface) {
                        $contentTypeBuilder->setIO($this->io);
                    }
                    $contentTypeBuilder->buildContentTypeConfiguration($contentTypeIdentifier, $contentTypeConfiguration, $provider);
                }
            }
        }
    }
}
