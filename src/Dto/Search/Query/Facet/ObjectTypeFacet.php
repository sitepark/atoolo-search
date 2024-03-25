<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Facet;

/**
 * @codeCoverageIgnore
 */
class ObjectTypeFacet extends FacetField
{
    /**
     * @param string[] $objectTypes
     */
    public function __construct(
        string $key,
        public readonly array $objectTypes,
        ?string $excludeFilter = null
    ) {
        parent::__construct(
            $key,
            'sp_objecttype',
            $objectTypes,
            $excludeFilter
        );
    }
}
