<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Result;

use ArrayIterator;
use Atoolo\Resource\Resource;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<Resource>
 * @codeCoverageIgnore
 */
class SearchResult implements IteratorAggregate
{
    /**
     * @param Resource[] $results
     * @param FacetGroup[] $facetGroups
     */
    public function __construct(
        public readonly int $total,
        public readonly int $limit,
        public readonly int $offset,
        public readonly array $results,
        public readonly array $facetGroups,
        public readonly int $queryTime
    ) {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }
}
