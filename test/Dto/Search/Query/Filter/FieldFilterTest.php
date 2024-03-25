<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Dto\Search\Query\Filter;

use Atoolo\Search\Dto\Search\Query\Filter\FieldFilter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FieldFilter::class)]
class FieldFilterTest extends TestCase
{
    public function testEmptyValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FieldFilter('test', []);
    }

    public function testGetQueryWithOneField(): void
    {
        $field = new FieldFilter('test', ['a']);
        $this->assertEquals(
            'test:a',
            $field->getQuery(),
            'unexpected query'
        );
    }

    public function testGetQueryWithTwoFields(): void
    {
        $field = new FieldFilter('test', ['a', 'b']);
        $this->assertEquals(
            'test:(a b)',
            $field->getQuery(),
            'unexpected query'
        );
    }

    public function testExclude(): void
    {
        $field = new FieldFilter('test', ['a']);
        $exclude = $field->exclude();
        $this->assertEquals(
            '-test:a',
            $exclude->getQuery(),
            'unexpected exclude query'
        );
    }
}
