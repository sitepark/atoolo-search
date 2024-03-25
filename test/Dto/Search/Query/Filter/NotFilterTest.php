<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Dto\Search\Query\Filter;

use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\Filter\NotFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NotFilter::class)]
class NotFilterTest extends TestCase
{
    public function testGetQuery(): void
    {
        $filter = $this->createStub(Filter::class);
        $filter->method('getQuery')
            ->willReturn('a:b');
        $notFilter = new NotFilter($filter);

        $this->assertEquals(
            'NOT a:b',
            $notFilter->getQuery(),
            'unexpected query'
        );
    }
}
