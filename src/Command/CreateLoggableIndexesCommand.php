<?php

namespace Locastic\ActivityLog\Command;

use Locastic\ActivityLog\Bridge\Elasticsearch\Index\ElasticsearchIndexFactoryInterface;
use Locastic\ActivityLog\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateLoggableIndexesCommand extends Command
{
    protected static $defaultName = 'locastic:create-loggable-indexes';

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Creating activity log indexes ...');
        $loggableContextCollection = $this->loggableContextCollectionFactory->create();

        foreach ($loggableContextCollection->getIterator() as $loggableClass => $config) {
            $this->io->writeln('Creating ' . $loggableClass . ' activity_log index');

            try {
                $this->elasticsearchIndexFactory->createActivityLogIndex($loggableClass);
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
            }

            $this->io->writeln('Creating ' . $loggableClass . ' current_data_tracker index');

            try {
                $this->elasticsearchIndexFactory->createCurrentDataTrackerLogIndex($loggableClass);
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
            }
        }

        $this->io->success('Done!');
        return 0;
    }
}
