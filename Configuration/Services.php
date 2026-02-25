<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use WEBcoast\Migrator\DependencyInjection\MigratorCollectionCompilerPass;
use WEBcoast\Migrator\Update\RecordDataMigratorCollection;

return function (ContainerConfigurator $container, ContainerBuilder $builder): void {
    $builder->addCompilerPass(new MigratorCollectionCompilerPass());

    $services = $container->services();

    $services
        ->set('webcoast.migrator.record_data_migrator_collection')
        ->class(RecordDataMigratorCollection::class)
        ->args([[]]);
};
