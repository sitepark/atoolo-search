<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Facet;

/**
 * @codeCoverageIgnore
 */
class FacetQuery extends Facet
{
    public function __construct(
        string $key,
        public readonly string $query,
        ?string $excludeFilter = null
    ) {
        parent::__construct($key, $excludeFilter);
    }
}
