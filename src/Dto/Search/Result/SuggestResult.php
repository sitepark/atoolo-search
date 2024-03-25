<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Result;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int,Suggestion>
 * @codeCoverageIgnore
 */
class SuggestResult implements IteratorAggregate
{
    /**
     * @param Suggestion[] $suggestions
     * @param int $queryTime
     */
    public function __construct(
        public readonly array $suggestions,
        public readonly int $queryTime
    ) {
    }

    /**
     * @return ArrayIterator<int,Suggestion>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->suggestions);
    }
}
