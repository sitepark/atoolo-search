<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Result;

/**
 * @codeCoverageIgnore
 */
class Suggestion
{
    public function __construct(
        public readonly string $term,
        public readonly int $hits
    ) {
    }
}
