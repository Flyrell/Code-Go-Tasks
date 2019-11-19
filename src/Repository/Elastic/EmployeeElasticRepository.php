<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Repository\Elastic;

use App\Document\DocumentInterface;
use App\Document\EmployeeDocument;

/**
 * @method EmployeeDocument|null find(string $id)
 * @method EmployeeDocument|null findOneBy(array $criteria)
 * @method EmployeeDocument|null createDocument(array $data)
 * @method EmployeeDocument|null updateDocument(DocumentInterface $document, array $data)
 */
class EmployeeElasticRepository extends AbstractElasticRepository
{
    /**
     * {@inheritdoc}
     */
    public function getDocumentClass(): string
    {
        return EmployeeDocument::class;
    }

    /**
     * @param array       $criteria
     * @param int         $offset
     * @param int         $limit
     * @param string|null $sort
     *
     * @return QueryResult
     */
    public function search(array $criteria, int $offset, int $limit, string $sort = null): QueryResult
    {
        $this->assertResultWindowSize($offset, $limit);

        $body = [
            'from' => $offset,
            'size' => $limit,
        ];

        $bool = [];
        $boostConditions = [];
        $orX = [];
        $must = [];
        $sort = [
            ['lastName' => 'lastName-asc' === $sort ? 'asc' : 'desc'],
            ['_id' => 'desc'],
        ];

        if (!empty($criteria['search'])) {
            $fulltextConditions = $this->buildFulltextSearchConditions($criteria['search']);

            foreach ($fulltextConditions['shouldConditions'] as $shouldCondition) {
                $orX[] = $shouldCondition;
            }

            foreach ($fulltextConditions['boostConditions'] as $boostCondition) {
                $boostConditions[] = $boostCondition;
            }
        }

        if (!empty($criteria['skills'])) {
            // TODO: Possible Bug - do not use nested query here, complain that skill filter does not work
            foreach ($criteria['skills'] as $skillId) {
                $must[] = $this->buildSkillCondition($skillId);
            }
        }

        if ($orX) {
            $must[] = ['bool' => ['should' => $orX]];
        }

        // Add must conditions to main query, if any
        if ($must) {
            $bool['must'] = $must;
        }

        if ($boostConditions) {
            // Sort by document score
            $sort = ['_score'];
            $bool['should'] = $boostConditions;
        }

        // Add query only when it is required
        if ($bool) {
            $body['query'] = ['bool' => $bool];
        }

        if ($sort) {
            $body['sort'] = $sort;
        }

        $params = [
            'index' => $this->getIndex(),
            'type' => ElasticRepositoryInterface::TYPE,
            'body' => $body,
        ];

        return $this->hydrateQueryResultResponse(
            $this->client->search($params)
        );
    }

    /**
     * Return condition where the employee must specified skill.
     *
     * @param int $skillId
     *
     * @return array
     */
    private function buildSkillCondition(int $skillId): array
    {
        return [
            'nested' => [
                'path' => 'skills',
                'score_mode' => 'avg',
                'query' => [
                    'bool' => [
                        'must' => ['term' => ['skills.id' => $skillId]],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $search
     *
     * @return array|null
     */
    private function buildFullNameConditions(string $search): ?array
    {
        $parts = array_filter(array_map(function ($part) {
            return trim($part);
        }, explode(' ', trim($search), 2)));

        if (!$parts) {
            return null;
        }

        $should = [];
        $combinations = [
            ['firstName', 'lastName'],
            ['lastName', 'firstName'],
        ];

        foreach ($combinations as $combination) {
            $must = [];
            foreach ($combination as $index => $field) {
                if (isset($parts[$index])) {
                    $must[] = [
                        'prefix' => [
                            $field => $parts[$index],
                        ],
                    ];
                }
            }
            $should[] = [
                'bool' => [
                    'must' => $must,
                ],
            ];
        }

        return ['bool' => [
            'should' => $should,
        ]];
    }

    private function buildFulltextSearchConditions(string $search): array
    {
        $searchedTerms = $this->tokenizeSearch($search);
        $lowercaseSearch = strtolower($search);

        // At least one of these conditions has to be met in order for the matched document to be present in results
        $shouldConditions = [
            ['term' => ['addresses.street.canonical' => $lowercaseSearch]],
            ['term' => ['addresses.city.canonical' => $lowercaseSearch]],
            ['terms' => ['addresses.city' => $searchedTerms]],
            ['terms' => ['addresses.street' => $searchedTerms]],
            ['terms' => ['addresses.postalCode' => $searchedTerms]],
            ['terms' => ['addresses.country' => $searchedTerms]],
        ];

        // If we have just one search phrase, we can afford to use the wildcard search
        if (1 === count($searchedTerms)) {
            $wildcardSearch = "*$search*";
            $shouldConditions[] = ['wildcard' => ['email' => $wildcardSearch]];
            $shouldConditions[] = ['wildcard' => ['phone' => $wildcardSearch]];
        } else {
            $shouldConditions[] = ['terms' => ['email' => $searchedTerms]];
            $shouldConditions[] = ['terms' => ['phone' => $searchedTerms]];
        }

        // Exact matches should result in higher score - we achieve this by using should alongside the must in the top level query
        $boostConditions = [
            ['term' => ['addresses.street.canonical' => ['value' => $lowercaseSearch, 'boost' => 2]]],
            ['term' => ['addresses.city.canonical' => ['value' => $lowercaseSearch, 'boost' => 2]]],
        ];

        if ($fullNameConditions = $this->buildFullNameConditions($search)) {
            $shouldConditions[] = $fullNameConditions;
        }

        return [
            'boostConditions' => $boostConditions,
            'shouldConditions' => $shouldConditions,
        ];
    }

    private function tokenizeSearch(string $search): array
    {
        return array_values(array_filter(preg_split("/[\s]+/", $search)));
    }

    /**
     * {@inheritdoc}
     */
    protected function getIndexParameters(): array
    {
        return [
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'lowercase' => [
                            'type' => 'custom',
                            'tokenizer' => 'keyword',
                            'filter' => [
                                'lowercase',
                                'asciifolding',
                            ],
                        ],
                        'edge_ngram' => [
                            'tokenizer' => 'edge_ngram',
                        ],
                    ],
                    'tokenizer' => [
                        // Allows for tokenized search of word with their beginnings
                        // For example, string "Hořická 57" can be matched by term "Hoř" or "Hořic" or "5"
                        'edge_ngram' => [
                            'type' => 'edge_ngram',
                            'min_gram' => 1,
                            'max_gram' => 12,
                            'token_chars' => [
                                'letter',
                                'digit',
                                // Include dot, to match token "III." in "Hrad III. nádvoří 8"
                                'punctuation',
                                // Allow symbols like @
                                'symbol',
                            ],
                        ],
                    ],
                ],
            ],
            'mappings' => [
                ElasticRepositoryInterface::TYPE => [
                    'dynamic' => 'strict',
                    'properties' => [
                        'fullName' => [
                            'type' => 'keyword',
                            'fields' => [
                                'canonical' => [
                                    'type' => 'text',
                                    'analyzer' => 'lowercase',
                                ],
                            ],
                        ],
                        'firstName' => [
                            'type' => 'keyword',
                            'copy_to' => 'fullName',
                        ],
                        'lastName' => [
                            'type' => 'keyword',
                            'copy_to' => 'fullName',
                        ],
                        'birthday' => [
                            'type' => 'date',
                        ],
                        'phone' => [
                            'analyzer' => 'edge_ngram',
                            'type' => 'text',
                        ],
                        'email' => [
                            'analyzer' => 'edge_ngram',
                            'type' => 'text',
                            'fields' => [
                                'canonical' => [
                                    'type' => 'text',
                                    'analyzer' => 'lowercase',
                                ],
                            ],
                        ],
                        'skills' => [
                            'type' => 'nested',
                            'properties' => [
                                'id' => [
                                    'type' => 'integer',
                                ],
                                'name' => [
                                    'type' => 'keyword',
                                ],
                                'level' => [
                                    'type' => 'integer',
                                ],
                            ],
                        ],
                        'addresses' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => [
                                    'type' => 'integer',
                                ],
                                'country' => [
                                    'type' => 'keyword',
                                ],
                                'city' => [
                                    'analyzer' => 'edge_ngram',
                                    'type' => 'text',
                                    'fields' => [
                                        'canonical' => [
                                            'type' => 'text',
                                            'analyzer' => 'lowercase',
                                        ],
                                    ],
                                ],
                                'street' => [
                                    'analyzer' => 'edge_ngram',
                                    'type' => 'text',
                                    'fields' => [
                                        'canonical' => [
                                            'type' => 'text',
                                            'analyzer' => 'lowercase',
                                        ],
                                    ],
                                ],
                                'postalCode' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                        'payments' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => [
                                    'type' => 'integer',
                                ],
                                'month' => [
                                    'type' => 'date',
                                ],
                                'amount' => [
                                    'type' => 'double',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
