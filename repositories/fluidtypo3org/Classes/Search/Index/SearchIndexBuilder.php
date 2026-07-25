<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Search\Index;

use FluidTYPO3\FluidTYPO3Org\Documentation\Folder;
use FluidTYPO3\FluidTYPO3Org\Documentation\Repository\DocumentationRepositoryInterface;
use FluidTYPO3\FluidTYPO3Org\Search\SearchContextExtractor;
use FluidTYPO3\FluidTYPO3Org\Search\SearchTextNormalizer;
use TYPO3\CMS\Core\Database\ConnectionPool;

final readonly class SearchIndexBuilder
{
    public const TABLE_NAME = 'tx_fluidtypo3org_search_index';

    private const GIST_TABLE = 'tx_fluidshare_domain_model_gist';
    private const TAG_TABLE = 'tx_fluidshare_domain_model_tag';
    private const EXTENSION_TABLE = 'tx_fluidshare_domain_model_extension';
    private const GIST_TAG_TABLE = 'tx_fluidshare_gist_tag_mm';
    private const GIST_EXTENSION_TABLE = 'tx_fluidshare_gist_extension_mm';

    public function __construct(
        private DocumentationRepositoryInterface $documentationRepository,
        private ConnectionPool $connectionPool,
        private SearchTextNormalizer $normalizer,
        private SearchContextExtractor $contextExtractor,
    ) {}

    public function rebuild(): SearchIndexRebuildResult
    {
        $entries = [];
        $this->collectDocumentationEntries(
            $this->documentationRepository->getRoot(),
            $entries,
        );
        array_push($entries, ...$this->collectCodeExampleEntries());

        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        $connection->transactional(function () use ($connection, $entries): void {
            $connection->executeStatement('DELETE FROM ' . self::TABLE_NAME);
            foreach ($entries as $entry) {
                $connection->insert(
                    self::TABLE_NAME,
                    $entry->toDatabaseRow($this->normalizer),
                );
            }
        });

        $countsByType = [];
        foreach ($entries as $entry) {
            $recordType = $entry->getRecordType();
            $countsByType[$recordType] = ($countsByType[$recordType] ?? 0) + 1;
        }
        ksort($countsByType);
        return new SearchIndexRebuildResult($countsByType);
    }

    /**
     * @param list<SearchIndexEntry> $entries
     */
    private function collectDocumentationEntries(Folder $folder, array &$entries): void
    {
        foreach ($folder->getDocuments() as $document) {
            $route = $document->getRoute()->getKey();
            $contextContent = implode("\n", [
                $route,
                $document->getTitle(),
                $document->getMarkdown(),
            ]);
            $entries[] = new SearchIndexEntry(
                'docs',
                $route,
                0,
                $route,
                $document->getTitle(),
                $document->getExcerpt(),
                $document->getMarkdown(),
                preg_split('/[-\/]+/', $route) ?: [],
                $this->contextExtractor->extractExtensionContexts($contextContent),
                $this->contextExtractor->extractFeatureContexts($contextContent),
            );
        }
        foreach ($folder->getFolders() as $childFolder) {
            $this->collectDocumentationEntries($childFolder, $entries);
        }
    }

    /**
     * @return list<SearchIndexEntry>
     */
    private function collectCodeExampleEntries(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::GIST_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $now = time();
        $rows = $queryBuilder
            ->select('uid', 'title', 'summary', 'slug')
            ->from(self::GIST_TABLE)
            ->where(
                $queryBuilder->expr()->eq('confirmed', 1),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0),
                $queryBuilder->expr()->eq('t3ver_wsid', 0),
                $queryBuilder->expr()->lte('starttime', $now),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('endtime', 0),
                    $queryBuilder->expr()->gt('endtime', $now),
                ),
            )
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $tagsByGist = $this->collectRelatedValues(
            self::GIST_TAG_TABLE,
            self::TAG_TABLE,
            'name',
        );
        $extensionsByGist = $this->collectRelatedValues(
            self::GIST_EXTENSION_TABLE,
            self::EXTENSION_TABLE,
            'extension_key',
        );

        $entries = [];
        foreach ($rows as $row) {
            $uid = (int)$row['uid'];
            $title = trim((string)$row['title']);
            $summary = trim((string)$row['summary']);
            $tags = $tagsByGist[$uid] ?? [];
            $extensions = $extensionsByGist[$uid] ?? [];
            $contextContent = implode("\n", [
                $title,
                $summary,
                implode(' ', $tags),
                implode(' ', $extensions),
            ]);
            $entries[] = new SearchIndexEntry(
                'code_example',
                (string)$uid,
                $uid,
                (string)$row['slug'],
                $title,
                $summary,
                $contextContent,
                $tags,
                $extensions,
                $this->contextExtractor->extractFeatureContexts($contextContent),
            );
        }
        return $entries;
    }

    /**
     * @return array<int, list<string>>
     */
    private function collectRelatedValues(
        string $relationTable,
        string $foreignTable,
        string $valueField,
    ): array {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($relationTable);
        $queryBuilder->getRestrictions()->removeAll();
        $rows = $queryBuilder
            ->select('relation.uid_local', 'foreign_record.' . $valueField)
            ->from($relationTable, 'relation')
            ->innerJoin(
                'relation',
                $foreignTable,
                'foreign_record',
                $queryBuilder->expr()->eq(
                    'foreign_record.uid',
                    $queryBuilder->quoteIdentifier('relation.uid_foreign'),
                ),
            )
            ->where($queryBuilder->expr()->eq('foreign_record.deleted', 0))
            ->orderBy('relation.sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        $valuesByGist = [];
        foreach ($rows as $row) {
            $value = trim((string)$row[$valueField]);
            if ($value !== '') {
                $valuesByGist[(int)$row['uid_local']][] = $value;
            }
        }
        return $valuesByGist;
    }
}
