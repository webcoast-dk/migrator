<?php

declare(strict_types=1);


namespace WEBcoast\Migrator\Command;


use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;
use WEBcoast\Migrator\Configuration\ContentTypeProviderCollection;
use WEBcoast\Migrator\Configuration\ContentTypeProviderInterface;
use WEBcoast\Migrator\Service\MigrationService;

#[AsCommand('migrator:config:from', 'Create content block configuration from an existing content element')]
class CreateConfigCommand extends Command
{
    protected SymfonyStyle $io;

    protected ContentTypeProviderCollection $contentTypeProviders;

    protected MigrationService $migrationUtility;

    #[Required]
    public function setDceRepository(ContentTypeProviderCollection $contentTypeProviders): void
    {
        $this->contentTypeProviders = $contentTypeProviders;
    }

    #[Required]
    public function setMigrationUtility(MigrationService $migrationUtility): void
    {
        $this->migrationUtility = $migrationUtility;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('contentTypeIdentifier', InputArgument::OPTIONAL, 'The combined identifier of provider and content type, e.g. "dce:dce_mydce", "dce:dce_dceuid34" or "flux:my_flux_content_type"');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        if (!$input->hasArgument('contentTypeIdentifier') || !$input->getArgument('contentTypeIdentifier')) {
            $contentTypeIdentifiers = array_merge(
                ...array_map(
                    fn (ContentTypeProviderInterface $provider) => array_map(
                        fn (array $contentType) => $provider->getIdentifier() . ':' . $contentType['identifier'],
                        $provider->getAvailableContentTypes()
                    ),
                    $this->contentTypeProviders->getProviders()
                )
            );
            $contentTypeQuestion = new Question('Which content type do you want to migrate?');
            $contentTypeQuestion->setAutocompleterValues($contentTypeIdentifiers);
            $contentTypeQuestion->setValidator(function (?string $identifier) use ($contentTypeIdentifiers) {
                if (empty($identifier) || !in_array($identifier, $contentTypeIdentifiers)) {
                    throw new \RuntimeException('Invalid content type identifier');
                }

                return $identifier;
            });

            $input->setArgument('contentTypeIdentifier', $this->io->askQuestion($contentTypeQuestion));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->migrationUtility->setIo($this->io);
        $this->migrationUtility->migrate($input->getArgument('contentTypeIdentifier'));

        return Command::SUCCESS;
    }
}
