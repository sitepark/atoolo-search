<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Dto\Search\Query;

use Atoolo\Search\Dto\Search\Query\Facet\Facet;
use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\QueryOperator;
use Atoolo\Search\Dto\Search\Query\SelectQueryBuilder;
use Atoolo\Search\Dto\Search\Query\Sort\Criteria;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SelectQueryBuilder::class)]
class SelectQueryBuilderTest extends TestCase
{
    private SelectQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new SelectQueryBuilder();
        $this->builder->index('myindex');
    }

    public function testBuildWithoutIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = new SelectQueryBuilder();
        $builder->build();
    }

    public function testSetEmptyIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = new SelectQueryBuilder();
        $builder->index('');
    }

    public function testSetIndex(): void
    {
        $builder = new SelectQueryBuilder();
        $builder->index('myindex');
        $query = $builder->build();

        $this->assertEquals(
            'myindex',
            $query->index,
            'unexpected index'
        );
    }

    public function testSetText(): void
    {
        $this->builder->text('abc');
        $query = $this->builder->build();
        $this->assertEquals('abc', $query->text, 'unexpected text');
    }

    public function testSetOffset(): void
    {
        $this->builder->offset(10);
        $query = $this->builder->build();
        $this->assertEquals(10, $query->offset, 'unexpected offset');
    }

    public function testSetInvalidOffset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->builder->offset(-1);
    }

    public function testSetLimit(): void
    {
        $this->builder->limit(10);
        $query = $this->builder->build();
        $this->assertEquals(10, $query->limit, 'unexpected limit');
    }

    public function testSetInvalidLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->builder->limit(-1);
    }

    public function testSetSort(): void
    {
        $criteria = $this->createStub(Criteria::class);

        $this->builder->sort($criteria);
        $query = $this->builder->build();
        $this->assertEquals([$criteria], $query->sort, 'unexpected sort');
    }

    public function testSetFilter(): void
    {
        $filter = $this->getMockBuilder(Filter::class)
            ->setConstructorArgs(['test'])
            ->getMock();
        $this->builder->filter($filter);
        $query = $this->builder->build();
        $this->assertEquals([$filter], $query->filter, 'unexpected filter');
    }

    public function testSetTwoFilterWithSameKey(): void
    {
        $filterA = $this->getMockBuilder(Filter::class)
            ->setConstructorArgs(['test'])
            ->getMock();
        $filterB = $this->getMockBuilder(Filter::class)
            ->setConstructorArgs(['test'])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->builder->filter($filterA, $filterB);
    }

    public function testSetFacet(): void
    {
        $facet = $this->getMockBuilder(Facet::class)
            ->setConstructorArgs(['test', null])
            ->getMock();
        $this->builder->facet($facet);
        $query = $this->builder->build();
        $this->assertEquals([$facet], $query->facets, 'unexpected facets');
    }

    public function testSetTwoFacetSWithSameKey(): void
    {
        $facetA = $this->getMockBuilder(Facet::class)
            ->setConstructorArgs(['test', null])
            ->getMock();
        $facetB = $this->getMockBuilder(Facet::class)
            ->setConstructorArgs(['test', null])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->builder->facet($facetA, $facetB);
    }

    public function testSetQueryDefaultOperator(): void
    {
        $this->builder->defaultQueryOperator(QueryOperator::AND);
        $query = $this->builder->build();
        $this->assertEquals(
            QueryOperator::AND,
            $query->defaultQueryOperator,
            'unexpected queryDefaultOperator'
        );
    }
}
