<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Search;

use FluidTYPO3\FluidTYPO3Org\Search\Index\SearchIndexBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;

final readonly class SearchRepository
{
    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    /**
     * @param list<string> $terms
     * @return list<array<string, mixed>>
     */
    public function findCandidates(string $normalizedQuery, array $terms): array
    {
        if ($normalizedQuery === '' || $terms === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(SearchIndexBuilder::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        $termConstraints = [];
        foreach ($terms as $term) {
            $containsTerm = $queryBuilder->createNamedParameter(
                '%' . $queryBuilder->escapeLikeWildcards($term) . '%',
            );
            $containsSetTerm = $queryBuilder->createNamedParameter(
                '%|' . $queryBuilder->escapeLikeWildcards($term) . '|%',
            );
            $termConstraints[] = $queryBuilder->expr()->or(
                $queryBuilder->expr()->like('title_normalized', $containsTerm),
                $queryBuilder->expr()->like('summary_normalized', $containsTerm),
                $queryBuilder->expr()->like('content_normalized', $containsTerm),
                $queryBuilder->expr()->like('tags', $containsSetTerm),
                $queryBuilder->expr()->like('extension_context', $containsSetTerm),
                $queryBuilder->expr()->like('feature_context', $containsSetTerm),
            );
        }

        return $queryBuilder
            ->select('*')
            ->from(SearchIndexBuilder::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        'title_normalized',
                        $queryBuilder->createNamedParameter($normalizedQuery),
                    ),
                    $queryBuilder->expr()->and(...$termConstraints),
                ),
            )
            ->setMaxResults(250)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function countAll(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(SearchIndexBuilder::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();
        return (int)$queryBuilder
            ->count('uid')
            ->from(SearchIndexBuilder::TABLE_NAME)
            ->executeQuery()
            ->fetchOne();
    }
}
