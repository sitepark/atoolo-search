<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Search;

use Atoolo\Search\Dto\Search\Query\SuggestQuery;
use Atoolo\Search\Dto\Search\Result\Suggestion;
use Atoolo\Search\Dto\Search\Result\SuggestResult;
use Atoolo\Search\Exception\UnexpectedResultException;
use Atoolo\Search\Service\SolrClientFactory;
use Atoolo\Search\SuggestSearcher;
use JsonException;
use Solarium\Core\Client\Client;
use Solarium\QueryType\Select\Query\Query as SolrSelectQuery;
use Solarium\QueryType\Select\Result\Result as SolrSelectResult;

/**
 * Implementation of the "suggest search" based on a Solr index.
 *
 *  @phpstan-type SolariumResponse array{
 *      facet_counts: array{
 *         facet_fields: array<string, array<string>>
 *      }
 *  }
 */
class SolrSuggest implements SuggestSearcher
{
    private const INDEX_SUGGEST_FIELD = 'raw_content';

    public function __construct(
        private readonly SolrClientFactory $clientFactory
    ) {
    }

    /**
     * @throws UnexpectedResultException
     */
    public function suggest(SuggestQuery $query): SuggestResult
    {
        $client = $this->clientFactory->create($query->index);

        $solrQuery = $this->buildSolrQuery($client, $query);
        $solrResult = $client->select($solrQuery);
        return $this->buildResult($solrResult);
    }

    private function buildSolrQuery(
        Client $client,
        SuggestQuery $query
    ): SolrSelectQuery {
        $solrQuery = $client->createSelect();
        $solrQuery->addParam("spellcheck", "true");
        $solrQuery->addParam("spellcheck.accuracy", "0.6");
        $solrQuery->addParam("spellcheck.onlyMorePopular", "false");
        $solrQuery->addParam("spellcheck.count", "15");
        $solrQuery->addParam("spellcheck.maxCollations", "5");
        $solrQuery->addParam("spellcheck.maxCollationTries", "15");
        $solrQuery->addParam("spellcheck.collate", "true");
        $solrQuery->addParam("spellcheck.collateExtendedResults", "true");
        $solrQuery->addParam("spellcheck.extendedResults", "true");
        $solrQuery->addParam("facet", "true");
        $solrQuery->addParam("facet.sort", "count");
        $solrQuery->addParam("facet.method", "enum");
        $solrQuery->addParam(
            "facet.prefix",
            $query->text
        );
        $solrQuery->addParam("facet.limit", $query->limit);
        $solrQuery->addParam("facet.field", self::INDEX_SUGGEST_FIELD);

        $solrQuery->setOmitHeader(false);
        $solrQuery->setStart(0);
        $solrQuery->setRows(0);

        // Filter
        foreach ($query->filter as $filter) {
            $filterQuery = $solrQuery->createFilterQuery($filter->key);
            $filterQuery->setQuery($filter->getQuery());
            $filterQuery->setTags($filter->tags);
        }

        return $solrQuery;
    }

    private function buildResult(
        SolrSelectResult $solrResult
    ): SuggestResult {
        $suggestions = $this->parseSuggestion(
            $solrResult->getResponse()->getBody()
        );
        return new SuggestResult(
            $suggestions,
            $solrResult->getQueryTime() ?? 0
        );
    }

    /**
     * @throws UnexpectedResultException
     * @return Suggestion[]
     */
    private function parseSuggestion(
        string $responseBody
    ): array {
        try {
            /** @var SolariumResponse $json */
            $json = json_decode(
                $responseBody,
                true,
                5,
                JSON_THROW_ON_ERROR
            );
            $facets =
                $json['facet_counts']['facet_fields'][self::INDEX_SUGGEST_FIELD]
                ?? [];

            $len = count($facets);

            $suggestions = [];
            for ($i = 0; $i < $len - 1; $i += 2) {
                $term = $facets[$i];
                $hits = (int)$facets[$i + 1];
                $suggestions[] = new Suggestion($term, $hits);
            }

            return $suggestions;
        } catch (JsonException $e) {
            throw new UnexpectedResultException(
                $responseBody,
                "Invalid JSON for suggest result",
                0,
                $e
            );
        }
    }
}
