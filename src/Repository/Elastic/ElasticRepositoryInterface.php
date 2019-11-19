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
use Iterator;

interface ElasticRepositoryInterface
{
    /**
     * @var string
     */
    const SERIALIZER_GROUP = 'storage';

    /**
     * Type name.
     *
     * @var string
     */
    const TYPE = 'doc';

    /**
     * Elastic datetime format.
     *
     * @var string
     */
    const ELASTIC_DATETIME_FORMAT = 'Y-m-d\TH:i:sP';

    /**
     * Elastic maximum result window size.
     *
     * @var int
     */
    const ELASTIC_MAX_RESULT_WINDOW_SIZE = 10000;

    /**
     * Returns index name.
     *
     * @return string
     */
    public function getIndex(): string;

    /**
     * @throws IndexAlreadyExistsException
     */
    public function createIndex(): void;

    /**
     * @throws IndexNotFoundException
     */
    public function deleteIndex(): void;

    /**
     * Finds a document by $id.
     *
     * @param string $id
     *
     * @return DocumentInterface|null
     */
    public function find(string $id): ?DocumentInterface;

    /**
     * Finds all objects in the repository.
     *
     * @param array $sort
     *
     * @return array the objects
     */
    public function findAll(array $sort = []): array;

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     *
     * @return Iterator
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): Iterator;

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     *
     * @return DocumentInterface|null
     */
    public function findOneBy(array $criteria): ?DocumentInterface;

    /**
     * Counts objects by a set of criteria.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy(array $criteria): int;

    /**
     * Counts objects by a query.
     *
     * @param array $query
     *
     * @return int
     */
    public function countByQuery(array $query): int;

    /**
     * Indexes the passed $document.
     *
     * @param DocumentInterface $document
     *
     * @return mixed
     */
    public function index(DocumentInterface $document);

    /**
     * @param array $criteria
     *
     * @return int Number of deleted documents
     */
    public function deleteBy(array $criteria): int;

    /**
     * Creates a new document with $data.
     *
     * @param array $data
     *
     * @return DocumentInterface Newly created document
     */
    public function createDocument(array $data): DocumentInterface;

    /**
     * Updates $document with $data.
     *
     * @param DocumentInterface $document
     * @param array             $data
     *
     * @return DocumentInterface New instance of updated document
     */
    public function updateDocument(DocumentInterface $document, array $data): DocumentInterface;

    /**
     * Get class implementing the DocumentInterface which is subject of this repository.
     *
     * @return string
     */
    public function getDocumentClass(): string;

    /**
     * Performs multiple index operation's in a bulk based on array of documents.
     *
     * @param array  $documents
     * @param string $refresh
     *
     * @return array
     *
     * @throws BulkOperationException
     */
    public function bulkIndex(array $documents, $refresh = 'wait_for'): array;
}
