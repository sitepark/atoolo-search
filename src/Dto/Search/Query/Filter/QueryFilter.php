<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

class QueryFilter extends Filter
{
    public function __construct(
        private readonly string $query,
        ?string $key = null,
    ) {
        parent::__construct(
            $key
        );
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
