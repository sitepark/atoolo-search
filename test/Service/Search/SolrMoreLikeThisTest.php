<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Search;

use Atoolo\Resource\Resource;
use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\MoreLikeThisQuery;
use Atoolo\Search\Service\Search\SolrMoreLikeThis;
use Atoolo\Search\Service\Search\SolrResultToResourceResolver;
use Atoolo\Search\Service\SolrClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\QueryType\MoreLikeThis\Query as SolrMoreLikeThisQuery;
use Solarium\QueryType\MoreLikeThis\Result as SolrMoreLikeThisResult;
use Solarium\QueryType\Select\Query\FilterQuery;

#[CoversClass(SolrMoreLikeThis::class)]
class SolrMoreLikeThisTest extends TestCase
{
    private Resource|Stub $resource;

    private SolrMoreLikeThis $searcher;

    protected function setUp(): void
    {
        $clientFactory = $this->createStub(
            SolrClientFactory::class
        );
        $client = $this->createStub(Client::class);
        $clientFactory->method('create')->willReturn($client);

        $query = $this->createStub(SolrMoreLikeThisQuery::class);
        $filterQuery = new FilterQuery();
        $query->method('createFilterQuery')->willReturn($filterQuery);

        $client->method('createMoreLikeThis')->willReturn($query);

        $result = $this->createStub(SolrMoreLikeThisResult::class);
        $client->method('execute')->willReturn($result);

        $this->resource = $this->createStub(Resource::class);

        $resultToResourceResolver = $this->createStub(
            SolrResultToResourceResolver::class
        );
        $resultToResourceResolver
            ->method('loadResourceList')
            ->willReturn([$this->resource]);

        $this->searcher = new SolrMoreLikeThis(
            $clientFactory,
            $resultToResourceResolver
        );
    }

    public function testMoreLikeThis(): void
    {
        $filter = $this->getMockBuilder(Filter::class)
            ->setConstructorArgs(['test', []])
            ->getMock();

        $query = new MoreLikeThisQuery(
            'myindex',
            '/test.php',
            [$filter]
        );

        $searchResult = $this->searcher->moreLikeThis($query);

        $this->assertEquals(
            [$this->resource],
            $searchResult->results,
            'unexpected results'
        );
    }
}
