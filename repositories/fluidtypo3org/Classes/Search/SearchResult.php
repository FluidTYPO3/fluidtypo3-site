<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Search;

use FluidTYPO3\FluidTYPO3Org\Documentation\DocumentationRoute;

final readonly class SearchResult
{
    /**
     * @param array{segment1?: string, segment2?: string, segment3?: string} $routeArguments
     */
    public function __construct(
        private string $type,
        private int $sourceUid,
        private string $title,
        private string $summary,
        private int $score,
        private array $routeArguments,
    ) {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row, int $score): self
    {
        $type = (string)$row['record_type'];
        $route = (string)$row['route'];
        return new self(
            $type,
            (int)$row['source_uid'],
            (string)$row['title'],
            (string)$row['summary'],
            $score,
            $type === 'docs' && $route !== ''
                ? DocumentationRoute::fromPath($route)->getArguments()
                : [],
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeLabel(): string
    {
        return $this->type === 'docs' ? 'Documentation' : 'Code example';
    }

    public function getSourceUid(): int
    {
        return $this->sourceUid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @return array{segment1?: string, segment2?: string, segment3?: string}
     */
    public function getRouteArguments(): array
    {
        return $this->routeArguments;
    }
}
