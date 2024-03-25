<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Facet;

/**
 * @codeCoverageIgnore
 */
class CategoryFacet extends FacetField
{
    /**
     * @param string[] $categories
     */
    public function __construct(
        string $key,
        array $categories,
        ?string $excludeFilter = null
    ) {
        parent::__construct(
            $key,
            'sp_category_path',
            $categories,
            $excludeFilter
        );
    }
}
