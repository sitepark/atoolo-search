<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Search;

use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\SuggestQuery;
use Atoolo\Search\Dto\Search\Result\Suggestion;
use Atoolo\Search\Exception\UnexpectedResultException;
use Atoolo\Search\Service\Search\SolrSuggest;
use Atoolo\Search\Service\SolrClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\Core\Client\Response;
use Solarium\QueryType\Select\Query\FilterQuery;
use Solarium\QueryType\Select\Query\Query as SolrSelectQuery;
use Solarium\QueryType\Select\Result\Result as SelectResult;

#[CoversClass(SolrSuggest::class)]
class SolrSuggestTest extends TestCase
{
    private SelectResult|Stub $result;

    private SolrSuggest $searcher;

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

        $client->method('createSelect')->willReturn($query);

        $this->result = $this->createStub(SelectResult::class);
        $client->method('select')->willReturn($this->result);

        $this->searcher = new SolrSuggest($clientFactory);
    }

    public function testSuggest(): void
    {
        $filter = $this->getMockBuilder(Filter::class)
            ->setConstructorArgs(['test', []])
            ->getMock();

        $query = new SuggestQuery(
            'myindex',
            'cat',
            [$filter]
        );

        $response = new Response(<<<END
{
    "facet_counts" : {
        "facet_fields" : {
            "raw_content" : [
                "category",
                10,
                "catalog",
                5
            ]
        }
    }
}
END);

        $this->result->method('getResponse')->willReturn($response);

        $suggestResult = $this->searcher->suggest($query);

        $expected = [
            new Suggestion('category', 10),
            new Suggestion('catalog', 5),
        ];

        $this->assertEquals(
            $expected,
            $suggestResult->suggestions,
            'unexpected suggestion'
        );
    }

    public function testEmptySuggest(): void
    {
        $query = new SuggestQuery(
            'myindex',
            'cat',
        );

        $response = new Response(<<<END
{
    "facet_counts" : {
    }
}
END);

        $this->result->method('getResponse')->willReturn($response);

        $suggestResult = $this->searcher->suggest($query);

        $this->assertEmpty(
            $suggestResult->suggestions,
            'suggestion should be empty'
        );
    }

    public function testInvalidSuggestResponse(): void
    {
        $query = new SuggestQuery(
            'myindex',
            'cat',
        );

        $response = new Response("none json");

        $this->result->method('getResponse')->willReturn($response);

        $this->expectException(UnexpectedResultException::class);
        $this->searcher->suggest($query);
    }
}
