<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Result;

/**
 * @codeCoverageIgnore
 */
class FacetGroup
{
    /**
     * @param Facet[] $facets
     */
    public function __construct(
        public readonly string $key,
        public readonly array $facets
    ) {
    }
}
