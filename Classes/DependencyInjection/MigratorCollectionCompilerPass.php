<?php

declare(strict_types=1);


namespace WEBcoast\Migrator\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WEBcoast\Migrator\Attribute\SourceContentType;
use WEBcoast\Migrator\Update\RecordDataMigrator;

class MigratorCollectionCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->has('webcoast.migrator.record_data_migrator_collection')) {
            return;
        }

        $definition = $container->findDefinition('webcoast.migrator.record_data_migrator_collection');
        $taggedServices = $container->findTaggedServiceIds('webcoast.migrator.record_data_migrator');

        $mapping = [];

        foreach ($taggedServices as $serviceId => $tags) {
            $serviceDefinition = $container->getDefinition($serviceId);
            $className = $serviceDefinition->getClass();

            if ($className === null) {
                continue;
            }

            $reflection = new \ReflectionClass($className);

            if (!$reflection->isSubclassOf(RecordDataMigrator::class)) {
                continue;
            }

            foreach ($reflection->getAttributes(SourceContentType::class) as $attribute) {
                /** @var SourceContentType $instance */
                $instance = $attribute->newInstance();
                if (isset($mapping[$instance->contentTypeIdentifier])) {
                    // This means that there are two migrators for the same content type, which is not allowed. This is a configuration error and should be fixed by the developer.
                    throw new \RuntimeException(sprintf('Conflicting migrators found for content type "%s". Migrator classes "%s" and "%s" want to migrate have the same content type.', $instance->contentTypeIdentifier, $mapping[$instance->contentTypeIdentifier]->getMigratorClass(), $className), 1772441135);
                }
                $mapping[$instance->contentTypeIdentifier] = sprintf('%s:%s:%s', $instance->providerIdentifier, $instance->contentTypeIdentifier, $className);
            }
        }

        $definition->setArgument(0, $mapping);
    }
}
