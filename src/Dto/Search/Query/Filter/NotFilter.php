<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

class NotFilter extends Filter
{
    public function __construct(
        private readonly Filter $filter,
        ?string $key = null,
        array $tags = []
    ) {
        parent::__construct($key, $tags);
    }

    public function getQuery(): string
    {
        return 'NOT ' . $this->filter->getQuery();
    }
}
