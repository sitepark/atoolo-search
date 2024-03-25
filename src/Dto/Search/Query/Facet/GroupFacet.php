<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Facet;

/**
 * @codeCoverageIgnore
 */
class GroupFacet extends FacetField
{
    /**
     * @param string[] $groups
     */
    public function __construct(
        string $key,
        public readonly array $groups,
        ?string $excludeFilter = null
    ) {
        parent::__construct(
            $key,
            'sp_group_path',
            $groups,
            $excludeFilter
        );
    }
}
