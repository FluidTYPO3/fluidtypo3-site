<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Collection;

use FluidTYPO3\FluidTYPO3Org\Documentation\Document;

/**
 * @implements \IteratorAggregate<int, Document>
 */
final readonly class DocumentCollection implements \Countable, \IteratorAggregate
{
    /** @var list<Document> */
    private array $items;

    public function __construct(Document ...$items)
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
