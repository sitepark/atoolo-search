<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Facet;

/**
 * @codeCoverageIgnore
 */
class FacetField extends Facet
{
    /**
     * @param string[] $terms
     */
    public function __construct(
        string $key,
        public readonly string $field,
        public readonly array $terms,
        ?string $excludeFilter = null
    ) {
        parent::__construct($key, $excludeFilter);
    }
}
