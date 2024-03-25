<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Dto\Search\Query\Filter;

use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\Filter\OrFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

#[CoversClass(OrFilter::class)]
class OrFilterTest extends TestCase
{
    public function testOrQuery(): void
    {
        $a = $this->createStub(Filter::class);
        $a->method('getQuery')
            ->willReturn('a');

        $b = $this->createStub(Filter::class);
        $b->method('getQuery')
            ->willReturn('b');

        $and = new OrFilter([$a, $b]);

        assertEquals(
            '(a OR b)',
            $and->getQuery(),
            'unexpected query'
        );
    }
}
