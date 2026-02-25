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
                $mapping[$instance->contentType] = $className;
            }
        }

        $definition->setArgument(0, $mapping);
    }
}
