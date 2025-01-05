<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

final class ElasticsearchClient
{
    public function __construct(
        private readonly string $activityLogElasticHost,
        private readonly ?string $activityLogElasticUser = null,
        private readonly ?string $activityLogElasticPassword = null,
        private readonly bool $activityLogElasticUseSSLVerification = true,
    ) {
    }

    public function getClient(): Client
    {
        $client = ClientBuilder::create()->setHosts([$this->activityLogElasticHost])
            ->setSSLVerification($this->activityLogElasticUseSSLVerification);

        if ($this->activityLogElasticUser !== null && $this->activityLogElasticPassword !== null) {
            $client->setBasicAuthentication($this->activityLogElasticUser, $this->activityLogElasticPassword);
        }

        return $client->build();
    }
}
