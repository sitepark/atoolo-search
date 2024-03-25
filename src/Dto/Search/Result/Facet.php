<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Result;

/**
 * @codeCoverageIgnore
 */
class Facet
{
    public function __construct(
        public readonly string $key,
        public readonly int $hits
    ) {
    }
}
