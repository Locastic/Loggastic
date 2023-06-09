<?php

namespace Locastic\Loggastic\Command;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Locastic\Loggastic\Bridge\Elasticsearch\Index\ElasticsearchIndexFactoryInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[\Symfony\Component\Console\Attribute\AsCommand('locastic:activity-logs:create-loggable-indexes')]
class CreateLoggableIndexesCommand extends Command
{
    protected function configure(): void
    {
        $this->setName(self::$defaultName);
    }

    public function __construct(private readonly LoggableContextCollectionFactoryInterface $loggableContextCollectionFactory, private readonly ElasticsearchIndexFactoryInterface $elasticsearchIndexFactory)
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

            try {
                $this->elasticsearchIndexFactory->createActivityLogIndex($loggableClass);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'resource_already_exists_exception')) {
                    $output->writeln('Index already exists, skipping.');
                } else {
                    throw $e;
                }
            }

            $io->writeln('Creating '.$loggableClass.' current_data_tracker index');

            try {
                $this->elasticsearchIndexFactory->createCurrentDataTrackerLogIndex($loggableClass);
            } catch (ClientResponseException $e) {
                if (strpos($e->getMessage(), 'resource_already_exists_exception')) {
                    $output->writeln('Index already exists, skipping.');
                } else {
                    throw $e;
                }
            }
        }

        $io->success('Done!');

        return Command::SUCCESS;
    }
}
