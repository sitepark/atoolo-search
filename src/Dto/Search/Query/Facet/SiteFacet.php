<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Facet;

/**
 * @codeCoverageIgnore
 */
class SiteFacet extends FacetField
{
    /**
     * @param string[] $sites
     */
    public function __construct(
        string $key,
        public readonly array $sites,
        ?string $excludeFilter = null
    ) {
        parent::__construct(
            $key,
            'sp_site',
            $sites,
            $excludeFilter
        );
    }
}
