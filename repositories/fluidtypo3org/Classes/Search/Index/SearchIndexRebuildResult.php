<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Search\Index;

final readonly class SearchIndexRebuildResult
{
    /**
     * @param array<string, int> $countsByType
     */
    public function __construct(
        private array $countsByType,
    ) {}

    public function getTotal(): int
    {
        return array_sum($this->countsByType);
    }

    /**
     * @return array<string, int>
     */
    public function getCountsByType(): array
    {
        return $this->countsByType;
    }
}
