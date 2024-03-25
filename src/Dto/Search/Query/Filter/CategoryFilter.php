<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

/**
 * @codeCoverageIgnore
 */
class CategoryFilter extends FieldFilter
{
    /**
     * @param string[] $category
     */
    public function __construct(
        array $category,
        ?string $key = null
    ) {
        parent::__construct(
            'sp_category_path',
            $category,
            $key
        );
    }
}
