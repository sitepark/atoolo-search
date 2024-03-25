<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

/**
 * @codeCoverageIgnore
 */
class ContentSectionTypeFilter extends FieldFilter
{
    /**
     * @param string[] $contentTypes
     */
    public function __construct(
        array $contentTypes,
        ?string $key = null,
    ) {
        parent::__construct(
            'sp_contenttype',
            $contentTypes,
            $key
        );
    }
}
