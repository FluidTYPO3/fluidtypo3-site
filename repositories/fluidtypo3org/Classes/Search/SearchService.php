<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Search;

final readonly class SearchService
{
    public function __construct(
        private SearchRepository $searchRepository,
        private SearchTextNormalizer $normalizer,
    ) {}

    /**
     * @return list<SearchResult>
     */
    public function search(string $query): array
    {
        $normalizedQuery = $this->normalizer->normalize($query);
        $terms = $this->normalizer->getTerms($normalizedQuery);
        if ($terms === []) {
            return [];
        }

        $results = [];
        foreach ($this->searchRepository->findCandidates($normalizedQuery, $terms) as $row) {
            $score = $this->calculateScore($row, $normalizedQuery, $terms);
            $results[] = SearchResult::fromDatabaseRow($row, $score);
        }
        usort(
            $results,
            static fn(SearchResult $left, SearchResult $right): int =>
                $right->getScore() <=> $left->getScore()
                ?: strcasecmp($left->getTitle(), $right->getTitle()),
        );
        return array_slice($results, 0, 50);
    }

    public function getIndexedRecordCount(): int
    {
        return $this->searchRepository->countAll();
    }

    /**
     * @param array<string, mixed> $row
     * @param list<string> $terms
     */
    private function calculateScore(array $row, string $query, array $terms): int
    {
        $title = (string)$row['title_normalized'];
        $summary = (string)$row['summary_normalized'];
        $content = (string)$row['content_normalized'];
        $tags = (string)$row['tags'];
        $extensionContext = (string)$row['extension_context'];
        $featureContext = (string)$row['feature_context'];

        $score = 0;
        if ($title === $query) {
            $score += 1200;
        } elseif (str_contains($title, $query)) {
            $score += 650;
        }
        if (str_contains($summary, $query)) {
            $score += 220;
        }
        if (str_contains($content, $query)) {
            $score += 80;
        }

        $specificTermMatches = 0;
        foreach ($terms as $term) {
            $matchedSpecificField = false;
            if ($this->containsToken($title, $term)) {
                $score += 180;
                $matchedSpecificField = true;
            } elseif (str_contains($title, $term)) {
                $score += 120;
                $matchedSpecificField = true;
            }
            if ($this->setContains($tags, $term)) {
                $score += 150;
                $matchedSpecificField = true;
            }
            if ($this->setContains($extensionContext, $term)) {
                $score += 140;
                $matchedSpecificField = true;
            }
            if ($this->setContains($featureContext, $term)) {
                $score += 130;
                $matchedSpecificField = true;
            }
            if ($this->containsToken($summary, $term)) {
                $score += 50;
            } elseif (str_contains($summary, $term)) {
                $score += 30;
            }
            if ($this->containsToken($content, $term)) {
                $score += 10;
            } elseif (str_contains($content, $term)) {
                $score += 5;
            }
            if ($matchedSpecificField) {
                $specificTermMatches++;
            }
        }
        if (count($terms) > 1 && $specificTermMatches === count($terms)) {
            $score += 200;
        }
        return $score;
    }

    private function containsToken(string $value, string $term): bool
    {
        return str_contains(' ' . $value . ' ', ' ' . $term . ' ');
    }

    private function setContains(string $set, string $term): bool
    {
        return str_contains($set, '|' . $term . '|');
    }
}
