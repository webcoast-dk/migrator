<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Configuration;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use WEBcoast\Migrator\Exception\UnknownProviderException;

readonly class ContentTypeProviderCollection
{
    public function __construct(
        #[AutowireIterator(tag: 'webcoast.migrator.content_type_provider')]
        protected iterable $contentTypeProviders
    ) {}

    /**
     * @return array|ContentTypeProviderInterface[]
     */
    public function getProviders(): array
    {
        return iterator_to_array($this->contentTypeProviders);
    }

    public function getProvider(string $identifier): ?ContentTypeProviderInterface
    {
        foreach ($this->contentTypeProviders as $provider) {
            if ($provider->getIdentifier() === $identifier) {
                return $provider;
            }
        }

        throw new UnknownProviderException(sprintf('Content type provider with identifier "%s" not found.', $identifier));
    }
}
