<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Dto\Search\Query\Filter;

use Atoolo\Search\Dto\Search\Query\Filter\QueryFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueryFilter::class)]
class QueryFilterTest extends TestCase
{
    public function testGetQuery(): void
    {
        $filter = new QueryFilter('a:b');
        $this->assertEquals(
            'a:b',
            $filter->getQuery(),
            'unexpected query'
        );
    }
}
