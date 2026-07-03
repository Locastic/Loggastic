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

        $io->title('Preparing activity log storage ...');
        $loggableContextCollection = $this->loggableContextCollectionFactory->create();

        foreach ($loggableContextCollection->getIterator() as $loggableClass => $config) {
            $io->writeln('Creating '.$loggableClass.' activity log storage');

            if (!$this->storageInitializer->initializeActivityLogStorage($loggableClass)) {
                $output->writeln('Already exists, skipping.');
            }

            $io->writeln('Creating '.$loggableClass.' current data tracker storage');

            if (!$this->storageInitializer->initializeCurrentDataTrackerStorage($loggableClass)) {
                $output->writeln('Already exists, skipping.');
            }
        }

        $io->success('Done!');

        return Command::SUCCESS;
    }
}
