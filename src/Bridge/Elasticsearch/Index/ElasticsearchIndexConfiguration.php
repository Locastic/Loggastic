<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Index;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextInterface;

class ElasticsearchIndexConfiguration implements ElasticsearchIndexConfigurationInterface
{
    private bool $dateDetection;
    private string $dateFormats;
    private array $activityLogProperties;
    private array $currentDataTrackerProperties;

    public function __construct(
        bool $dateDetection,
        string $dateFormats,
        array $activityLogProperties,
        array $currentDataTrackerProperties
    ) {
        $this->dateDetection = $dateDetection;
        $this->dateFormats = $dateFormats;
        $this->activityLogProperties = $activityLogProperties;
        $this->currentDataTrackerProperties = $currentDataTrackerProperties;
    }

    public function getActivityLogIndexConfig(ElasticsearchContextInterface $elasticsearchContext): array
    {
        return [
            'index' => $elasticsearchContext->getActivityLogIndex(),
            'body' => [
                'mappings' => [
                    'date_detection' => $this->dateDetection,
                    'dynamic_date_formats' => [$this->dateFormats],
                    'properties' => $this->activityLogProperties,
                ],
            ],
        ];
    }

    public function getCurrentDataTrackerIndexConfig(ElasticsearchContextInterface $elasticsearchContext): array
    {
        return [
            'index' => $elasticsearchContext->getCurrentDataTrackerIndex(),
            'body' => [
                'mappings' => [
                    'date_detection' => $this->dateDetection,
                    'dynamic_date_formats' => [$this->dateFormats],
                    'properties' => $this->currentDataTrackerProperties,
                ],
            ],
        ];
    }
}
