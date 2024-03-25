<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

/**
 * @codeCoverageIgnore
 */
class GroupFilter extends FieldFilter
{
    /**
     * @param string[] $group
     */
    public function __construct(
        array $group,
        ?string $key = null,
    ) {
        parent::__construct(
            'sp_group_path',
            $group,
            $key
        );
    }
}
