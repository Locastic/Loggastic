<?php

namespace Locastic\ActivityLog\Bridge\Elasticsearch;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ElasticsearchService
{
    private ElasticsearchClient $elasticsearchClient;
    private NormalizerInterface $normalizer;

    public function __construct(ElasticsearchClient $elasticsearchClient, NormalizerInterface $normalizer)
    {
        $this->elasticsearchClient = $elasticsearchClient;
        $this->normalizer = $normalizer;
    }

    public function createItem($item, string $index)
    {
        $body = $this->normalizer->normalize($item, 'array');

        $request = [
            'index' => $index,
            'id' => (string)$item->getId(),
            'body' => $body,
            'refresh' => true
        ];

        $this->elasticsearchClient->getClient()->create($request);
    }

    public function getItemById($id, string $index, string $denormalizeToClass)
    {
        $documents = $this->elasticsearchClient->getClient()->search([
            'index' => $index,
            'body' => ['query' => ['term' => ["id" => $id]]],
        ]);

        if ($documents['hits']['total'] !== 1) {
            return null;
        }

        $data = $documents['hits']['hits'][0]['_source'];

        return $this->normalizer->denormalize($data, $denormalizeToClass);
    }

    public function getItemByQuery(string $index, string $denormalizeToClass, array $body = [])
    {
        $documents = $this->elasticsearchClient->getClient()->search([
            'index' => $index,
            'body' => $body,
        ]);

        if ($documents['hits']['total']['value'] !== 1) {
            return null;
        }

        $data = $documents['hits']['hits'][0]['_source'];

        return $this->normalizer->denormalize($data, $denormalizeToClass, 'array');
    }

    public function updateItem($id, $item, string $index)
    {
        $body = $this->normalizer->normalize($item, 'array');

        $request = [
            'index' => $index,
            'id' => (string)$id,
            'body' => ['doc' => $body],
            'refresh' => true,
            'retry_on_conflict' => 1
        ];

        $this->elasticsearchClient->getClient()->update($request);
    }

    public function getCollection(string $index, string $denormalizeToClass, array $body = [], $limit = 20, $offset = 0): array
    {
        $documents = $this->elasticsearchClient->getClient()->search([
            'index' => $index,
            'from' => $offset,
            'size' => $limit,
            'body' => $body,
        ]);

        $results = [];
        foreach ($documents['hits']['hits'] as $document) {
            $results[] = $this->normalizer->denormalize($document['_source'], $denormalizeToClass, 'array');
        }

        return $results;
    }
}
