<?php

namespace Locastic\Loggastic\Bridge\Elasticsearch;

use Locastic\Loggastic\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ElasticsearchService
{
    use ElasticNormalizationContextTrait;

    private ElasticsearchClient $elasticsearchClient;
    private NormalizerInterface $normalizer;
    private DenormalizerInterface $denormalizer;

    public function __construct(ElasticsearchClient $elasticsearchClient, NormalizerInterface $normalizer, DenormalizerInterface $denormalizer)
    {
        $this->elasticsearchClient = $elasticsearchClient;
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    public function createItem($item, string $index, array $groups = [])
    {
        $body = $this->normalizer->normalize($item, 'array', $this->getNormalizationContext(['groups' => $groups]));
        $body['id'] = $item->getId();

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

            $normalizedItem = $this->normalizer->normalize($item, 'array', $this->getNormalizationContext(['groups' => $groups]));
            $normalizedItem['id'] = $item->getId();

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

    public function getItemById($id, string $index, string $denormalizeToClass)
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

    public function getItemByQuery(string $index, string $denormalizeToClass, array $body = [])
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
        $body = $this->normalizer->normalize($item, 'array', $this->getNormalizationContext(['groups' => $groups]));

        $request = [
            'index' => $index,
            'id' => (string) $id,
            'body' => ['doc' => $body],
            'refresh' => true,
            'retry_on_conflict' => 1,
        ];

        $this->elasticsearchClient->getClient()->update($request);
    }

    public function bulkUpdate(array $items, string $index, array $groups = []): void
    {
        $params = [];
        foreach ($items as $i => $item) {
            if (0 === $i % 2) {
                echo '.';
            }

            $normalizedItem = $this->normalizer->normalize($item, 'array', $this->getNormalizationContext(['groups' => $groups]));
            $normalizedItem['id'] = $item->getId();

            $params[] = [
                'update' => [
                    '_index' => $index,
                    '_id' => $item->getId(),
                ],
            ];
            $params[] = ['doc' => $normalizedItem];
        }

        $request = [
            'index' => $index,
            'body' => $params,
        ];

        $this->elasticsearchClient->getClient()->bulk($request);
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
