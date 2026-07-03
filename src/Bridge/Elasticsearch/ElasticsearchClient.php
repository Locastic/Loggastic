<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

final class ElasticsearchClient implements ElasticsearchClientInterface
{
    public function __construct(
        private readonly string $activityLogElasticHost,
        private readonly ?string $activityLogElasticUser = null,
        private readonly ?string $activityLogElasticPassword = null,
        private readonly bool $activityLogElasticSslVerification = true,
    ) {
    }

    public function getClient(): Client
    {
        $builder = ClientBuilder::create()
            ->setHosts([$this->activityLogElasticHost])
            ->setSSLVerification($this->activityLogElasticSslVerification);

        if (null !== $this->activityLogElasticUser && null !== $this->activityLogElasticPassword) {
            $builder->setBasicAuthentication($this->activityLogElasticUser, $this->activityLogElasticPassword);
        }

        return $builder->build();
    }
}
