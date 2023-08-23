<?php

namespace Locastic\Loggastic\Command;

use Doctrine\Persistence\ManagerRegistry;
use Locastic\Loggastic\Bridge\Elasticsearch\Index\ElasticsearchIndexFactoryInterface;
use Locastic\Loggastic\Message\PopulateCurrentDataTrackersMessage;
use Locastic\Loggastic\Metadata\LoggableContext\Factory\LoggableContextCollectionFactoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Recreate CurrentDataTracker index
 * Pull all loggable objects from DB and populate currentTracker data to elastic.
 */
#[AsCommand('locastic:activity-logs:populate-current-data-trackers')]
final class PopulateCurrentDataTrackersCommand extends Command
{
    public function __construct(private readonly ElasticsearchIndexFactoryInterface $elasticsearchIndexFactory, private readonly LoggableContextCollectionFactoryInterface $loggableContextCollectionFactory, private readonly ManagerRegistry $managerRegistry, private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // List all loggable classes
        $loggableContextCollection = iterator_to_array($this->loggableContextCollectionFactory->create()->getIterator());

        $loggableClasses = array_keys($loggableContextCollection);
        $loggableClasses[] = 'ALL';

        // aks for the class
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Choose loggable class to repopulate current data:',
            $loggableClasses,
            0
        );
        $question->setErrorMessage('Loggable class %s is invalid.');

        $loggableClass = $helper->ask($input, $output, $question);

        // ask for the limit
        $question = new Question(
            'Limit number of latest objects to be populated (leave empty for no limit):',
            0
        );

        $limit = $helper->ask($input, $output, $question);

        $io = new SymfonyStyle($input, $output);

        $io->title('Creating current data log trackers ...');

        if ('ALL' !== $loggableClass) {
            $loggableClasses = [$loggableClass];
        }

        foreach ($loggableClasses as $loggableClass) {
            if ('ALL' === $loggableClass) {
                continue;
            }

            try {
                $this->elasticsearchIndexFactory->recreateCurrentDataTrackerLogIndex($loggableClass);
            } catch (\Exception $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }

            // get repository for current loggable class and pull all data from DB
            $manager = $this->managerRegistry->getManagerForClass($loggableClass);

            // loggable class not mapped to the db, skip
            if (!$manager) {
                continue;
            }

            $io->title('Processing '.$loggableClass);
            $repository = $manager->getRepository($loggableClass);

            $batchSize = 250;
            $messagesCount = 0;
            $count = $repository->count([]);

            if (0 === $limit || (int) $limit > $count) {
                $limit = $count;
            }

            for ($offset = 0; $offset < $limit; $offset += $batchSize) {
                $this->bus->dispatch(new PopulateCurrentDataTrackersMessage($offset, $batchSize, $loggableClass, $loggableContextCollection[$loggableClass]));
                ++$messagesCount;
            }

            $io->success('Dispatched '.$messagesCount.' messages for populating '.$loggableClass.' data trackers');
        }

        return Command::SUCCESS;
    }
}
