<?php

namespace Locastic\Loggastic\Command;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Locastic\Loggastic\Bridge\Elasticsearch\Index\ElasticsearchIndexFactoryInterface;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateLoggableIndexesCommand extends Command
{
    protected static $defaultName = 'locastic:activity-logs:create-loggable-indexes';

    private LoggableContextCollectionFactoryInterface $loggableContextCollectionFactory;
    private ElasticsearchIndexFactoryInterface $elasticsearchIndexFactory;

    protected function configure(): void
    {
        $this->setName(self::$defaultName);
    }

    public function __construct(LoggableContextCollectionFactoryInterface $loggableContextCollectionFactory, ElasticsearchIndexFactoryInterface $elasticsearchIndexFactory)
    {
        parent::__construct();
        $this->loggableContextCollectionFactory = $loggableContextCollectionFactory;
        $this->elasticsearchIndexFactory = $elasticsearchIndexFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Creating activity log indexes ...');
        $loggableContextCollection = $this->loggableContextCollectionFactory->create();

        foreach ($loggableContextCollection->getIterator() as $loggableClass => $config) {
            $this->io->writeln('Creating '.$loggableClass.' activity_log index');

            try {
                $this->elasticsearchIndexFactory->createActivityLogIndex($loggableClass);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'resource_already_exists_exception')) {
                    $output->writeln('Index already exists, skipping.');
                } else {
                    throw $e;
                }
            }

            $this->io->writeln('Creating '.$loggableClass.' current_data_tracker index');

            try {
                $this->elasticsearchIndexFactory->createCurrentDataTrackerLogIndex($loggableClass);
            } catch (BadRequest400Exception $e) {
                if (strpos($e->getMessage(), 'resource_already_exists_exception')) {
                    $output->writeln('Index already exists, skipping.');
                } else {
                    throw $e;
                }
            }
        }

        $this->io->success('Done!');

        return Command::SUCCESS;
    }
}
