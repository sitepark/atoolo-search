<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

/**
 * @codeCoverageIgnore
 */
class ObjectTypeFilter extends FieldFilter
{
    /**
     * @param string[] $objectTypes
     */
    public function __construct(
        array $objectTypes,
        ?string $key = null,
    ) {
        parent::__construct(
            'sp_objecttype',
            $objectTypes,
            $key
        );
    }
}
