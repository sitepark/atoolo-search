<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Dto\Search\Query\Filter;

use Atoolo\Search\Dto\Search\Query\Filter\AndFilter;
use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

#[CoversClass(AndFilter::class)]
class AndFilterTest extends TestCase
{
    public function testAndQuery(): void
    {
        $a = $this->createStub(Filter::class);
        $a->method('getQuery')
            ->willReturn('a');

        $b = $this->createStub(Filter::class);
        $b->method('getQuery')
            ->willReturn('b');

        $and = new AndFilter([$a, $b]);

        assertEquals(
            '(a AND b)',
            $and->getQuery(),
            'unexpected query'
        );
    }
}
