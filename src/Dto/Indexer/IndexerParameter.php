<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Indexer;

class IndexerParameter
{
    /**
     * @param string[] $paths
     */
    public function __construct(
        public readonly string $index,
        public readonly int $cleanupThreshold = 0,
        public readonly int $chunkSize = 500,
        public readonly array $paths = []
    ) {
        if ($this->chunkSize < 10) {
            throw new \InvalidArgumentException(
                'chunk Size must be greater than 9'
            );
        }
    }
}
