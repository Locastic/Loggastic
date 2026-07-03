<?php

namespace Locastic\Loggastic\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Locastic\Loggastic\Factory\CurrentDataTrackerInputFactoryInterface;
use Locastic\Loggastic\Message\PopulateCurrentDataTrackersMessage;
use Locastic\Loggastic\Serializer\Traits\NormalizationContextTrait;
use Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsMessageHandler]
final class PopulateCurrentDataTrackersHandler
{
    use NormalizationContextTrait;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly CurrentDataTrackerInputFactoryInterface $currentDataTrackerInputFactory,
        private readonly NormalizerInterface $objectNormalizer,
        private readonly CurrentDataTrackerStorageInterface $currentDataTrackerStorage,
    ) {
    }

    public function __invoke(PopulateCurrentDataTrackersMessage $message): void
    {
        $loggableContext = $message->getLoggableContext();

        $manager = $this->managerRegistry->getManagerForClass($message->getLoggableClass());
        $repository = $manager->getRepository($message->getLoggableClass());

        $args = [];
        if (method_exists($message->getLoggableClass(), 'getCreatedAt')) { // todo move to config or command args
            $args = ['createdAt' => 'DESC'];
        }

        // todo move order to config or command
        $data = $repository->findBy([], $args, $message->getBatchSize(), $message->getOffset());

        echo "\r\n";
        echo 'Creating '.$message->getBatchSize().' current data trackers for '.$message->getLoggableClass().' ...'."\r\n";
        echo "\r\n";

        $currentDataTrackers = [];
        foreach ($data as $item) {
            echo 'Processing object '.$item->getId()."\r\n";

            $normalizedItem = $this->objectNormalizer->normalize($item, 'activityLog', $this->getNormalizationContext($loggableContext));
            $currentDataTrackers[] = $this->currentDataTrackerInputFactory->create($item, $normalizedItem);
        }

        $this->currentDataTrackerStorage->bulkSave($currentDataTrackers, $message->getLoggableClass());
    }
}
