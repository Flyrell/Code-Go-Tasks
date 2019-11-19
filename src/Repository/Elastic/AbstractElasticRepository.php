<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Repository\Elastic;

use App\Document\DocumentInterface;
use App\Repository\Elastic\Exception\BulkOperationException;
use App\Repository\Elastic\Exception\IndexAlreadyExistsException;
use App\Repository\Elastic\Exception\IndexNotFoundException;
use App\Repository\Elastic\Exception\ResultWindowSizeExceededException;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Iterator;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractElasticRepository implements ElasticRepositoryInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Config     $config
     * @param Client     $client
     * @param Serializer $serializer
     */
    public function __construct(Config $config, Client $client, Serializer $serializer)
    {
        $this->config = $config;
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(): string
    {
        return $this->config->getIndexPrefix().'-'.$this->getBareIndex();
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(): void
    {
        $index = $this->getIndex();
        $params = [
            'index' => $index,
            'body' => $this->getIndexParameters(),
        ];

        $indices = $this->client->indices();
        try {
            $indices->create($params);
        } catch (BadRequest400Exception $e) {
            throw new IndexAlreadyExistsException(sprintf('Index "%s" already exists', $index), null, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function indexExists(): bool
    {
        $params = [
            'index' => $this->getIndex(),
        ];

        return $this->client->indices()->exists($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(): void
    {
        $index = $this->getIndex();
        $params = [
            'index' => $index,
        ];
        $indices = $this->client->indices();

        try {
            $indices->delete($params);
        } catch (Missing404Exception $e) {
            throw new IndexNotFoundException(sprintf('Index "%s" does not exist', $index), null, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById(string $id): void
    {
        $this->client->delete([
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'id' => $id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?DocumentInterface
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'id' => $id,
        ];

        try {
            $data = $this->client->get($params);
        } catch (Missing404Exception $e) {
            return null;
        }

        return $this->hydrateDocument($data);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(array $sort = []): array
    {
        $response = $this->client->search([
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'body' => [
                'query' => [
                    'match_all' => new \stdClass(),
                ],
                'sort' => $sort,
                'size' => self::ELASTIC_MAX_RESULT_WINDOW_SIZE,
            ],
        ]);

        return $this->hydrateArrayResponse($response);
    }

    /**
     * Performs multiple index operation's in a bulk based on array of documents.
     *
     * @param array       $documents
     * @param string|bool $refresh
     *
     * @return array
     *
     * @throws BulkOperationException
     */
    public function bulkIndex(array $documents, $refresh = 'wait_for'): array
    {
        $body = [];
        foreach ($documents as $document) {
            $this->preIndex($document);
            $documentBody = $this->normalizeDocument($document);
            unset($documentBody['id']); // 'id' is needless here

            $body[] = [
                'index' => [
                    '_index' => $this->getIndex(),
                    '_type' => ElasticRepositoryInterface::TYPE,
                    '_id' => $document->getId(),
                ],
            ];
            $body[] = $documentBody;
        }

        $bulkParams = [
            'body' => $body,
        ];

        if (false !== $refresh) {
            $bulkParams['refresh'] = $refresh;
        }

        $response = $this->client->bulk($bulkParams);

        $errors = $response['errors'] ?? false;

        if ($errors) {
            throw new BulkOperationException($response, 'Bulk operation failed with errors.');
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): Iterator
    {
        return $this->getIteratorFromQuery($this->findByQuery($criteria), $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorFromQuery(array $query, array $orderBy = null, $limit = null, $offset = null): Iterator
    {
        $body = [
            'query' => $query,
            'size' => $limit ?? self::ELASTIC_MAX_RESULT_WINDOW_SIZE,
        ];

        if (isset($orderBy)) {
            $body['sort'] = $orderBy;
        }

        if (isset($offset)) {
            $body['from'] = $offset;
        }

        $params = [
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'body' => $body,
        ];

        $response = $this->client->search($params);

        return $this->hydrateIterableResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function countBy(array $criteria): int
    {
        $query = $this->findByQuery($criteria);

        return $this->countByQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function countByQuery(array $query): int
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'body' => [
                'query' => $query,
            ],
        ];

        $response = $this->client->count($params);

        return (int) ($response['count'] ?? 0);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    protected function findByQuery(array $criteria): array
    {
        $must = [];

        foreach ($criteria as $term => $value) {
            $must[] = [
                'term' => [
                    $term => $value,
                ],
            ];
        }

        return $this->createBoolMustQuery($must);
    }

    /**
     * @param array $must
     *
     * @return array
     */
    protected function createBoolMustQuery(array $must)
    {
        return [
            'bool' => [
                'must' => $must,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria): ?DocumentInterface
    {
        $must = [];

        foreach ($criteria as $term => $value) {
            $must[] = [
                'term' => [
                    $term => $value,
                ],
            ];
        }

        $response = $this->client->search([
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $must,
                    ],
                ],
                'size' => 1,
            ],
        ]);
        $data = $response['hits']['hits'][0] ?? null;

        return $data ? $this->hydrateDocument($data) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function index(DocumentInterface $document)
    {
        $this->preIndex($document);

        $id = $document->getId();
        $body = $this->normalizeDocument($document);
        unset($body['id']); // 'id' is needless here

        // Hint: You can use 'scope' key in $context to direct normalization outcome.
        $partial = $context['partial'] ?? false;
        if (true === $partial) {
            $body = [
                'doc' => $body,
            ];
        }

        $result = $this->client->index([
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'refresh' => 'wait_for',
            'id' => $id,
            'body' => $body,
        ]);

        if (!$id) {
            $document->setId($result['_id']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBy(array $criteria): int
    {
        $result = $this->client->deleteByQuery([
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'refresh' => true,
            'body' => [
                'query' => $this->findByQuery($criteria),
            ],
        ]);

        return $result['deleted'];
    }

    /**
     * {@inheritdoc}
     */
    public function createDocument(array $data): DocumentInterface
    {
        unset($data['id']);

        $document = $this->serializer->denormalize($data, $this->getDocumentClass());

        return $document;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDocument(DocumentInterface $document, array $data): DocumentInterface
    {
        unset($data['id']);

        $result = $this->preUpdate($document, $data);
        $result = array_merge($this->normalizeDocument($document), $result);

        /** @var DocumentInterface $document */
        $document = $this->serializer->denormalize($result, $this->getDocumentClass());

        $this->postUpdate($document, $data);

        return $document;
    }

    /**
     * @param $document
     *
     * @return array
     */
    protected function normalizeDocument($document): array
    {
        return $this->serializer->normalize($document, null, [
            'groups' => [static::SERIALIZER_GROUP],
        ]);
    }

    /**
     * @param $rawData
     *
     * @return DocumentInterface
     */
    protected function hydrateDocument($rawData): DocumentInterface
    {
        $data = $rawData['_source'];
        $data['id'] = $rawData['_id'];

        return $this->serializer->denormalize($data, $this->getDocumentClass());
    }

    /**
     * Pre-index hook.
     *
     * @param DocumentInterface $document
     */
    protected function preIndex(DocumentInterface $document)
    {
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     * Pre-update hook.
     *
     * @param DocumentInterface $document
     * @param array             $data
     *
     * @return array Data
     */
    protected function preUpdate(DocumentInterface $document, array $data): array
    {
        return $data;
    }

    /**
     * Post-update hook.
     *
     * @param DocumentInterface $document
     * @param array             $data
     */
    protected function postUpdate(DocumentInterface $document, array $data)
    {
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function hydrateArrayResponse(array $response): array
    {
        $documents = [];
        $result = $response['hits']['hits'] ?? [];

        foreach ($result as $data) {
            $documents[] = $this->hydrateDocument($data);
        }

        return $documents;
    }

    /**
     * @param array $response
     *
     * @return Iterator
     */
    protected function hydrateIterableResponse(array $response): Iterator
    {
        $result = $response['hits']['hits'] ?? [];

        foreach ($result as $data) {
            yield $this->hydrateDocument($data);
        }
    }

    /**
     * @param array  $response
     * @param string $scroll   (e.g.: '30s')
     *
     * @return Iterator
     */
    protected function hydrateScrollableResponse(array $response, string $scroll): Iterator
    {
        while (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
            foreach ($response['hits']['hits'] as $data) {
                yield $this->hydrateDocument($data);
            }

            $response = $this->client->scroll([
                'scroll_id' => $response['_scroll_id'],
                'scroll' => $scroll,
            ]);
        }
    }

    /**
     * @param array $response
     *
     * @return QueryResult
     */
    protected function hydrateQueryResultResponse(array $response): QueryResult
    {
        $result = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total'] ?? 0;

        $documents = [];
        foreach ($result as $data) {
            $documents[] = $this->hydrateDocument($data);
        }

        return new QueryResult($documents, $total);
    }

    /**
     * Make sure offset + limit don't exceed self::ELASTIC_MAX_RESULT_WINDOW_SIZE.
     *
     * @param int $offset
     * @param int $limit
     *
     * @throws ResultWindowSizeExceededException
     */
    protected function assertResultWindowSize(int $offset, int $limit): void
    {
        if (($offset + $limit) > self::ELASTIC_MAX_RESULT_WINDOW_SIZE) {
            throw new ResultWindowSizeExceededException();
        }
    }

    /**
     * Returns the bare index name (without a prefix).
     *
     * @return string
     */
    protected function getBareIndex(): string
    {
        $className = get_class($this);
        $name = substr($className, strrpos($className, '\\') + 1);
        $name = str_replace('ElasticRepository', '', $name);
        $name = trim($name, '\\');
        $name = strtolower($name);

        return $name;
    }

    /**
     * Update index mapping.
     *
     * @param array $properties
     */
    public function updateIndexMapping(array $properties)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'body' => [
                'properties' => $properties,
            ],
        ];

        $this->client->indices()->putMapping($params);
    }

    /**
     * @return array
     */
    abstract protected function getIndexParameters(): array;
}
