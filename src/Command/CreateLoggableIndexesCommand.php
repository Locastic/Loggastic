<?php

namespace Locastic\Loggastic\Command;

use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface;
use Locastic\Loggastic\Storage\StorageInitializerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('locastic:activity-logs:create-loggable-indexes')]
final class CreateLoggableIndexesCommand extends Command
{
    public function __construct(private readonly LoggableContextCollectionFactoryInterface $loggableContextCollectionFactory, private readonly StorageInitializerInterface $storageInitializer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating activity log indexes ...');
        $loggableContextCollection = $this->loggableContextCollectionFactory->create();

        foreach ($loggableContextCollection->getIterator() as $loggableClass => $config) {
            $io->writeln('Creating '.$loggableClass.' activity_log index');

            if (!$this->storageInitializer->initializeActivityLogStorage($loggableClass)) {
                $output->writeln('Index already exists, skipping.');
            }

            $io->writeln('Creating '.$loggableClass.' current_data_tracker index');

            if (!$this->storageInitializer->initializeCurrentDataTrackerStorage($loggableClass)) {
                $output->writeln('Index already exists, skipping.');
            }
        }

        $io->success('Done!');

        return Command::SUCCESS;
    }
}
