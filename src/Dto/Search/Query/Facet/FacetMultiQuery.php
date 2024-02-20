<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Facet;

class FacetMultiQuery extends Facet
{
    /**
     * @param FacetQuery[] $queries
     * @param string|null $excludeFilter
     */
    public function __construct(
        string $key,
        public readonly array $queries,
        ?string $excludeFilter
    ) {
        parent::__construct($key, $excludeFilter);
    }
}
