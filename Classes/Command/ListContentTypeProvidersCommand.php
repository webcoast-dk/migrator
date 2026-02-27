<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;
use WEBcoast\Migrator\Provider\ContentTypeProviderCollection;
use WEBcoast\Migrator\Provider\ContentTypeProviderInterface;

#[AsCommand(name: 'migrator:provider:list', description: 'Lists all available content type providers')]
class ListContentTypeProvidersCommand extends Command
{
    protected ContentTypeProviderCollection $providers;

    #[Required]
    public function setProviders(ContentTypeProviderCollection $providers): void
    {
        $this->providers = $providers;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $providers = $this->providers->getProviders();

        if (empty($providers)) {
            $output->writeln('<comment>No content type providers found.</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<info>Available Content Type Providers:</info>');

        $io = new SymfonyStyle($input, $output);
        $io->table([
            'Identifier',
            'Description'
        ], array_map(fn (ContentTypeProviderInterface $provider) => [$provider->getIdentifier(), $provider->getDescription()], $providers));

        return Command::SUCCESS;
    }
}
