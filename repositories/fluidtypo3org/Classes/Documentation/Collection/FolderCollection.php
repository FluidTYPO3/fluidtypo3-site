<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Collection;

use FluidTYPO3\FluidTYPO3Org\Documentation\Folder;

/**
 * @implements \IteratorAggregate<int, Folder>
 */
final readonly class FolderCollection implements \Countable, \IteratorAggregate
{
    /** @var list<Folder> */
    private array $items;

    public function __construct(Folder ...$items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
