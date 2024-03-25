<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

class OrFilter extends Filter
{
    /**
     * @param Filter[] $filter
     * @param string[] $tags
     */
    public function __construct(
        private readonly array $filter,
        ?string $key = null,
        array $tags = []
    ) {
        parent::__construct($key, $tags);
    }

    public function getQuery(): string
    {
        $query = [];
        foreach ($this->filter as $filter) {
            $query[] = $filter->getQuery();
        }

        return '(' . implode(' OR ', $query) . ')';
    }
}
