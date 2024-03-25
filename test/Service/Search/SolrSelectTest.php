<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Search;

use Atoolo\Resource\Resource;
use Atoolo\Search\Dto\Search\Query\Facet\Facet;
use Atoolo\Search\Dto\Search\Query\Facet\FacetMultiQuery;
use Atoolo\Search\Dto\Search\Query\Facet\FacetQuery;
use Atoolo\Search\Dto\Search\Query\Facet\ObjectTypeFacet;
use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\QueryOperator;
use Atoolo\Search\Dto\Search\Query\SelectQuery;
use Atoolo\Search\Dto\Search\Query\Sort\Criteria;
use Atoolo\Search\Dto\Search\Query\Sort\Date;
use Atoolo\Search\Dto\Search\Query\Sort\Headline;
use Atoolo\Search\Dto\Search\Query\Sort\Name;
use Atoolo\Search\Dto\Search\Query\Sort\Natural;
use Atoolo\Search\Dto\Search\Query\Sort\Score;
use Atoolo\Search\Service\Search\SolrQueryModifier;
use Atoolo\Search\Service\Search\SolrResultToResourceResolver;
use Atoolo\Search\Service\Search\SolrSelect;
use Atoolo\Search\Service\SolrClientFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\Component\FacetSet;
use Solarium\Component\Result\Facet\Field;
use Solarium\QueryType\Select\Query\FilterQuery;
use Solarium\QueryType\Select\Query\Query as SolrSelectQuery;
use Solarium\QueryType\Select\Result\Result as SelectResult;

#[CoversClass(SolrSelect::class)]
class SolrSelectTest extends TestCase
{
    private Resource|Stub $resource;

    private SelectResult|Stub $result;

    private SolrSelect $searcher;

    protected function setUp(): void
    {
        $clientFactory = $this->createStub(
            SolrClientFactory::class
        );
        $client = $this->createStub(Client::class);
        $clientFactory->method('create')->willReturn($client);

        $query = $this->createStub(SolrSelectQuery::class);

        $query->method('createFilterQuery')
            ->willReturn(new FilterQuery());
        $query->method('getFacetSet')
            ->willReturn(new FacetSet());

        $client->method('createSelect')->willReturn($query);

        $this->result = $this->createStub(SelectResult::class);
        $client->method('execute')->willReturn($this->result);

        $this->resource = $this->createStub(Resource::class);

        $resultToResourceResolver = $this->createStub(
            SolrResultToResourceResolver::class
        );

        $solrQueryModifier = $this->createStub(SolrQueryModifier::class);
        $solrQueryModifier->method('modify')->willReturn($query);

        $resultToResourceResolver
            ->method('loadResourceList')
            ->willReturn([$this->resource]);

        $this->searcher = new SolrSelect(
            $clientFactory,
            $resultToResourceResolver,
            [$solrQueryModifier],
        );
    }

    public function testSelectEmpty(): void
    {
        $query = new SelectQuery(
            'myindex',
            '',
            0,
            1,
            [
            ],
            [],
            [],
            QueryOperator::OR
        );

        $searchResult = $this->searcher->select($query);

        $this->assertEquals(
            [$this->resource],
            $searchResult->results,
            'unexpected results'
        );
    }

    public function testSelectWithText(): void
    {
        $query = new SelectQuery(
            'myindex',
            'cat dog',
            0,
            10,
            [
            ],
            [],
            [],
            QueryOperator::OR
        );

        $searchResult = $this->searcher->select($query);

        $this->assertEquals(
            [$this->resource],
            $searchResult->results,
            'unexpected results'
        );
    }

    public function testSelectWithSort(): void
    {
        $query = new SelectQuery(
            'myindex',
            '',
            0,
            10,
            [
                new Name(),
                new Headline(),
                new Date(),
                new Natural(),
                new Score(),
            ],
            [],
            [],
            QueryOperator::OR
        );

        $searchResult = $this->searcher->select($query);

        $this->assertEquals(
            [$this->resource],
            $searchResult->results,
            'unexpected results'
        );
    }

    public function testSelectWithInvalidSort(): void
    {
        $sort = $this->createStub(Criteria::class);

        $query = new SelectQuery(
            'myindex',
            '',
            0,
            10,
            [$sort],
            [],
            [],
            QueryOperator::OR
        );

        $this->expectException(InvalidArgumentException::class);
        $this->searcher->select($query);
    }

    public function testSelectWithAndDefaultOperator(): void
    {
        $query = new SelectQuery(
            'myindex',
            '',
            0,
            10,
            [],
            [],
            [],
            QueryOperator::AND
        );

        $searchResult = $this->searcher->select($query);

        $this->assertEquals(
            [$this->resource],
            $searchResult->results,
            'unexpected results'
        );
    }

    public function testSelectWithFilter(): void
    {
        $filter = $this->getMockBuilder(Filter::class)
            ->setConstructorArgs(['test', []])
            ->getMock();

        $query = new SelectQuery(
            'myindex',
            '',
            0,
            10,
            [],
            [$filter],
            [],
            QueryOperator::OR
        );

        $searchResult = $this->searcher->select($query);

        $this->assertEquals(
            [$this->resource],
            $searchResult->results,
            'unexpected results'
        );
    }

    public function testSelectWithFacets(): void
    {

        $facets = [
            new ObjectTypeFacet('objectType', ['content'], 'ob'),
            new FacetQuery('query', 'sp_id:123', 'ob'),
            new FacetMultiQuery(
                'multiquery',
                [new FacetQuery('query', 'sp_id:123', null)],
                'ob'
            )
        ];

        $query = new SelectQuery(
            'myindex',
            '',
            0,
            10,
            [],
            [],
            $facets,
            QueryOperator::OR
        );

        $searchResult = $this->searcher->select($query);

        $this->assertEquals(
            [$this->resource],
            $searchResult->results,
            'unexpected results'
        );
    }

    public function testSelectWithInvalidFacets(): void
    {

        $facets = [
            $this->createStub(Facet::class)
        ];

        $query = new SelectQuery(
            'myindex',
            '',
            0,
            10,
            [],
            [],
            $facets,
            QueryOperator::OR
        );

        $this->expectException(InvalidArgumentException::class);
        $this->searcher->select($query);
    }

    public function testResultFacets(): void
    {

        $facet = new Field([
            'content' => 10,
            'media' => 5
        ]);
        $facetSet = new \Solarium\Component\Result\FacetSet([
            'objectType' => $facet
        ]);

        $this->result->method('getFacetSet')
            ->willReturn($facetSet);

        $facets = [
            new ObjectTypeFacet('objectType', ['content'], 'ob'),
            new FacetQuery('query', 'sp_id:123', 'ob'),
            new FacetMultiQuery(
                'multiquery',
                [new FacetQuery('query', 'sp_id:123', null)],
                'ob'
            )
        ];

        $query = new SelectQuery(
            'myindex',
            '',
            0,
            10,
            [],
            [],
            $facets,
            QueryOperator::OR
        );

        $searchResult = $this->searcher->select($query);

        $this->assertEquals(
            'objectType',
            $searchResult->facetGroups[0]->key,
            'unexpected results'
        );
    }

    public function testInvalidResultFacets(): void
    {

        $facet = new Field([
            'content' => 'nonint',
        ]);
        $facetSet = new \Solarium\Component\Result\FacetSet([
            'objectType' => $facet
        ]);

        $this->result->method('getFacetSet')
            ->willReturn($facetSet);

        $facets = [
            new ObjectTypeFacet('objectType', ['content'], 'ob'),
            new FacetQuery('query', 'sp_id:123', 'ob'),
            new FacetMultiQuery(
                'multiquery',
                [new FacetQuery('query', 'sp_id:123', null)],
                'ob'
            )
        ];

        $query = new SelectQuery(
            'myindex',
            '',
            0,
            10,
            [],
            [],
            $facets,
            QueryOperator::OR
        );

        $this->expectException(InvalidArgumentException::class);
        $this->searcher->select($query);
    }
}
