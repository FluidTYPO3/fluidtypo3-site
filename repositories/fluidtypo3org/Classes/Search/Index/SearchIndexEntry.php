<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Search\Index;

use FluidTYPO3\FluidTYPO3Org\Search\SearchTextNormalizer;

final readonly class SearchIndexEntry
{
    /**
     * @param list<string> $tags
     * @param list<string> $extensionContexts
     * @param list<string> $featureContexts
     */
    public function __construct(
        private string $recordType,
        private string $sourceIdentifier,
        private int $sourceUid,
        private string $route,
        private string $title,
        private string $summary,
        private string $content,
        private array $tags,
        private array $extensionContexts,
        private array $featureContexts,
    ) {}

    /**
     * @return array<string, int|string>
     */
    public function toDatabaseRow(SearchTextNormalizer $normalizer): array
    {
        return [
            'record_type' => mb_strtolower(trim($this->recordType), 'UTF-8'),
            'source_identifier' => mb_strtolower(trim($this->sourceIdentifier), 'UTF-8'),
            'source_uid' => $this->sourceUid,
            'route' => mb_strtolower(trim($this->route), 'UTF-8'),
            'title' => $this->title,
            'title_normalized' => $normalizer->normalize($this->title),
            'summary' => $this->summary,
            'summary_normalized' => $normalizer->normalize($this->summary),
            'content_normalized' => $normalizer->normalize($this->content),
            'tags' => $normalizer->normalizeSet($this->tags),
            'extension_context' => $normalizer->normalizeSet($this->extensionContexts),
            'feature_context' => $normalizer->normalizeSet($this->featureContexts),
        ];
    }

    public function getRecordType(): string
    {
        return $this->recordType;
    }
}
