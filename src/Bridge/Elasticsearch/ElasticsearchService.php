<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ElasticsearchService
{
    use ElasticNormalizationContextTrait;

    public function __construct(private readonly ElasticsearchClient $elasticsearchClient, private readonly NormalizerInterface $normalizer, private readonly DenormalizerInterface $denormalizer)
    {
    }

    public function createItem($item, string $index, array $groups = []): void
    {
        $body = $this->normalizer->normalize($item, 'array', $this->getNormalizationContext($groups));

        $request = [
            'index' => $index,
            'body' => $body,
            'refresh' => true,
        ];

        $this->elasticsearchClient->getClient()->index($request);
    }

    public function bulkCreate(array $items, string $index, array $groups = []): void
    {
        $params = [];
        foreach ($items as $i => $item) {
            if (0 === $i % 100) {
                echo '.';
            }

            $normalizedItem = $this->normalizer->normalize($item, 'array', $this->getNormalizationContext($groups));

            $params[] = [
                'index' => [
                    '_index' => $index,
                    '_id' => $item->getId(),
                ],
            ];
            $params[] = $normalizedItem;
        }

        $request = [
            'index' => $index,
            'body' => $params,
        ];

        $this->elasticsearchClient->getClient()->bulk($request);
    }

    public function getItemById($id, string $index, string $denormalizeToClass): mixed
    {
        $documents = $this->elasticsearchClient->getClient()->search([
            'index' => $index,
            'body' => ['query' => ['term' => ['id' => $id]]],
        ]);

        if (1 !== $documents['hits']['total']) {
            return null;
        }

        $data = $documents['hits']['hits'][0]['_source'];
        $data['id'] = $documents['hits']['hits'][0]['_id'];

        return $this->denormalizer->denormalize($data, $denormalizeToClass);
    }

    public function getItemByQuery(string $index, string $denormalizeToClass, array $body = []): mixed
    {
        $documents = $this->elasticsearchClient->getClient()->search([
            'index' => $index,
            'body' => $body,
        ]);

        if (1 !== $documents['hits']['total']['value']) {
            return null;
        }

        $data = $documents['hits']['hits'][0]['_source'];
        $data['id'] = $documents['hits']['hits'][0]['_id'];

        return $this->denormalizer->denormalize($data, $denormalizeToClass, 'array');
    }

    public function updateItem($id, $item, string $index, array $groups = []): void
    {
        $body = $this->normalizer->normalize($item, 'array', $this->getNormalizationContext($groups));

        $request = [
            'index' => $index,
            'id' => (string) $id,
            'body' => ['doc' => $body],
            'refresh' => true,
            'retry_on_conflict' => 1,
        ];

        $this->elasticsearchClient->getClient()->update($request);
    }

    public function getCollection(string $index, ?string $denormalizeToClass = null, array $body = [], $limit = 20, $offset = 0): array
    {
        $documents = $this->elasticsearchClient->getClient()->search([
            'index' => $index,
            'from' => $offset,
            'size' => $limit,
            'body' => $body,
        ]);

        $results = [];
        foreach ($documents['hits']['hits'] as $document) {
            $document['_source']['id'] = $document['_id'];
            $results[] = $this->denormalizer->denormalize($document['_source'], $denormalizeToClass, 'elasticsearch', [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true]);
        }

        return $results;
    }
}
