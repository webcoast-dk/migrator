<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;
use WEBcoast\Migrator\Provider\ContentTypeProviderCollection;
use WEBcoast\Migrator\Exception\UnknownProviderException;

#[AsCommand(name: 'migrator:content-types:list', description: 'Lists all content types for a given provider')]
class ListContentTypesCommand extends Command
{
    protected ContentTypeProviderCollection $providers;

    #[Required]
    public function setProviders(ContentTypeProviderCollection $providers): void
    {
        $this->providers = $providers;
    }

    protected function configure(): void
    {
        $this->addArgument('provider', InputArgument::REQUIRED, 'Provider name. Check `migrator:provider:list` for available providers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $provider = $this->providers->getProvider(($input->getArgument('provider')));
            $contentTypes = $provider->getAvailableContentTypes();
            $io->info('Available Content Types:');
            $io->table([
                'Identifier',
                'Title',
                'Description'
            ], array_map(fn (array $contentType) => [$contentType['identifier'], $contentType['title'], $contentType['description']], $contentTypes));

            return Command::SUCCESS;
        } catch (UnknownProviderException) {
            $io->block(sprintf('Unknown provider: "<fg=white;bg=red;options=bold>%s</>". Please check `migrator:provider:list` for available providers.', $input->getArgument('provider')), 'ERROR', 'fg=white;bg=red', ' ', true, false);

            return Command::FAILURE;
        }
    }
}
