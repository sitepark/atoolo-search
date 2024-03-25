<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Facet;

/**
 * @codeCoverageIgnore
 */
class ContentSectionTypeFacet extends FacetField
{
    /**
     * @param string[] $contentSectionTypes
     */
    public function __construct(
        string $key,
        array $contentSectionTypes,
        ?string $excludeFilter = null
    ) {
        parent::__construct(
            $key,
            'sp_contenttype',
            $contentSectionTypes,
            $excludeFilter
        );
    }
}
