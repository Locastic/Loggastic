<?php

namespace Locastic\Loggastic\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\Loggastic\Factory\ActivityLogFactory;
use Locastic\Loggastic\Message\PopulateCurrentDataTrackersMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsMessageHandler]
class PopulateCurrentDataTrackersHandler
{
    use ElasticNormalizationContextTrait;

    private ManagerRegistry $managerRegistry;
    private ActivityLogFactory $activityLogFactory;
    private NormalizerInterface $objectNormalizer;
    private ElasticsearchService $elasticService;
    private ElasticsearchContextFactoryInterface $elasticsearchContextFactory;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ActivityLogFactory $activityLogFactory,
        NormalizerInterface $objectNormalizer,
        ElasticsearchService $elasticService,
        ElasticsearchContextFactoryInterface $elasticsearchContextFactory
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->objectNormalizer = $objectNormalizer;
        $this->activityLogFactory = $activityLogFactory;
        $this->elasticService = $elasticService;
        $this->elasticsearchContextFactory = $elasticsearchContextFactory;
    }

    public function __invoke(PopulateCurrentDataTrackersMessage $message): void
    {
        $loggableContext = $message->getLoggableContext();

        $manager = $this->managerRegistry->getManagerForClass($message->getLoggableClass());
        $repository = $manager->getRepository($message->getLoggableClass());

        $args = [];
        if (method_exists($message->getLoggableClass(), 'getCreatedAt')) { //todo move to config or command args
            $args = ['createdAt' => 'DESC'];
        }

        //todo move order to config or command
        $data = $repository->findBy([], $args, $message->getBatchSize(), $message->getOffset());

        echo "\r\n";
        echo 'Creating '.$message->getBatchSize().' current data trackers for '.$message->getLoggableClass().' ...'."\r\n";
        echo "\r\n";

        $currentDataTrackers = [];
        foreach ($data as $item) {
            echo 'Processing object '.$item->getId()."\r\n";

            $normalizedItem = $this->objectNormalizer->normalize($item, 'activityLog', $this->getNormalizationContext($loggableContext));
            $currentDataTrackers[] = $this->activityLogFactory->createCurrentDataTracker($item, $normalizedItem);
        }

        $elasticContext = $this->elasticsearchContextFactory->create($message->getLoggableClass());
        $this->elasticService->bulkCreate($currentDataTrackers, $elasticContext->getCurrentDataTrackerIndex(), ['current_data_tracker']);
    }
}
