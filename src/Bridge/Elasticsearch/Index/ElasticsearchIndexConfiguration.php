<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch\Index;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\ElasticsearchContextInterface;

final class ElasticsearchIndexConfiguration implements ElasticsearchIndexConfigurationInterface
{
    public function __construct(private readonly bool $dateDetection, private readonly string $dateFormats, private readonly array $activityLogProperties, private readonly array $currentDataTrackerProperties)
    {
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
