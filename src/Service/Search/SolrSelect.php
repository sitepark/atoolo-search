<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Search;

use Atoolo\Search\Dto\Search\Query\Facet\FacetField;
use Atoolo\Search\Dto\Search\Query\Facet\FacetMultiQuery;
use Atoolo\Search\Dto\Search\Query\Facet\FacetQuery;
use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\QueryOperator;
use Atoolo\Search\Dto\Search\Query\SelectQuery;
use Atoolo\Search\Dto\Search\Query\Sort\Criteria;
use Atoolo\Search\Dto\Search\Query\Sort\Date;
use Atoolo\Search\Dto\Search\Query\Sort\Headline;
use Atoolo\Search\Dto\Search\Query\Sort\Name;
use Atoolo\Search\Dto\Search\Query\Sort\Natural;
use Atoolo\Search\Dto\Search\Query\Sort\Score;
use Atoolo\Search\Dto\Search\Result\Facet;
use Atoolo\Search\Dto\Search\Result\FacetGroup;
use Atoolo\Search\Dto\Search\Result\SearchResult;
use Atoolo\Search\SelectSearcher;
use Atoolo\Search\Service\SolrClientFactory;
use InvalidArgumentException;
use Solarium\Component\Facet\Field;
use Solarium\Core\Client\Client;
use Solarium\QueryType\Select\Query\Query as SolrSelectQuery;
use Solarium\QueryType\Select\Result\Result as SelectResult;

/**
 * Implementation of the searcher on the basis of a Solr index.
 */
class SolrSelect implements SelectSearcher
{
    /**
     * @param iterable<SolrQueryModifier> $solrQueryModifierList
     */
    public function __construct(
        private readonly SolrClientFactory $clientFactory,
        private readonly SolrResultToResourceResolver $resultToResourceResolver,
        private readonly iterable $solrQueryModifierList = []
    ) {
    }

    public function select(SelectQuery $query): SearchResult
    {
        $client = $this->clientFactory->create($query->index);

        $solrQuery = $this->buildSolrQuery($client, $query);
        /** @var SelectResult $result */
        $result = $client->execute($solrQuery);
        return $this->buildResult($query, $result);
    }

    private function buildSolrQuery(
        Client $client,
        SelectQuery $query
    ): SolrSelectQuery {

        $solrQuery = $client->createSelect();

        // supplements the query with standard values, e.g. for boosting
        foreach ($this->solrQueryModifierList as $solrQueryModifier) {
            $solrQuery = $solrQueryModifier->modify($solrQuery);
        }

        $solrQuery->setStart($query->offset);
        $solrQuery->setRows($query->limit);

        // to get query-time
        $solrQuery->setOmitHeader(false);

        $this->addSortToSolrQuery($solrQuery, $query->sort);
        $this->addRequiredFieldListToSolrQuery($solrQuery);
        $this->addTextFilterToSolrQuery($solrQuery, $query->text);
        $this->addQueryDefaultOperatorToSolrQuery(
            $solrQuery,
            $query->defaultQueryOperator
        );
        $this->addFilterQueriesToSolrQuery(
            $solrQuery,
            $query->filter
        );
        $this->addFacetListToSolrQuery(
            $solrQuery,
            $query->facets
        );

        return $solrQuery;
    }

    /**
     * @param Criteria[] $criteriaList
     */
    private function addSortToSolrQuery(
        SolrSelectQuery $solrQuery,
        array $criteriaList
    ): void {
        $sorts = [];
        foreach ($criteriaList as $criteria) {
            if ($criteria instanceof Name) {
                $field = 'sp_name';
            } elseif ($criteria instanceof Headline) {
                $field = 'sp_title';
            } elseif ($criteria instanceof Date) {
                $field = 'sp_date';
            } elseif ($criteria instanceof Natural) {
                $field = 'sp_sortvalue';
            } elseif ($criteria instanceof Score) {
                $field = 'score';
            } else {
                throw new InvalidArgumentException(
                    'unsupported sort criteria: ' . get_class($criteria)
                );
            }

            $direction = strtolower($criteria->direction->name);

            $sorts[$field] = $direction;
        }
        $solrQuery->setSorts($sorts);
    }

    private function addRequiredFieldListToSolrQuery(
        SolrSelectQuery $solrQuery
    ): void {
        $solrQuery->setFields(['url', 'title', 'sp_id']);
    }

    private function addTextFilterToSolrQuery(
        SolrSelectQuery $solrQuery,
        string $text
    ): void {
        if (empty($text)) {
            return;
        }
        $terms = explode(' ', $text);
        $terms = array_map(
            fn ($term) => $solrQuery->getHelper()->escapeTerm(trim($term)),
            $terms
        );
        $text = implode(' ', $terms);
        $solrQuery->setQuery($text);
    }

    private function addQueryDefaultOperatorToSolrQuery(
        SolrSelectQuery $solrQuery,
        QueryOperator $operator
    ): void {
        $solrQuery->setQueryDefaultOperator(
            $operator === QueryOperator::OR
                ? SolrSelectQuery::QUERY_OPERATOR_OR
                : SolrSelectQuery::QUERY_OPERATOR_AND
        );
    }

    /**
     * @param Filter[] $filterList
     */
    private function addFilterQueriesToSolrQuery(
        SolrSelectQuery $solrQuery,
        array $filterList
    ): void {

        foreach ($filterList as $filter) {
            $key = $filter->key ?? uniqid('', true);
            $filterQuery = $solrQuery->createFilterQuery($key);
            $filterQuery->setQuery($filter->getQuery());
            $filterQuery->setTags($filter->tags);
        }
    }

    /**
     * @param \Atoolo\Search\Dto\Search\Query\Facet\Facet[] $facetList
     */
    private function addFacetListToSolrQuery(
        SolrSelectQuery $solrQuery,
        array $facetList
    ): void {
        foreach ($facetList as $facet) {
            if ($facet instanceof FacetField) {
                $this->addFacetFieldToSolrQuery($solrQuery, $facet);
            } elseif ($facet instanceof FacetQuery) {
                $this->addFacetQueryToSolrQuery($solrQuery, $facet);
            } elseif ($facet instanceof FacetMultiQuery) {
                $this->addFacetMultiQueryToSolrQuery($solrQuery, $facet);
            } else {
                throw new InvalidArgumentException(
                    'Unsupported facet-class ' . get_class($facet)
                );
            }
        }
    }

    /**
     * https://solarium.readthedocs.io/en/stable/queries/select-query/building-a-select-query/components/facetset-component/facet-field/
     */
    private function addFacetFieldToSolrQuery(
        SolrSelectQuery $solrQuery,
        FacetField $facet
    ): void {
        $facetSet = $solrQuery->getFacetSet();
        $field = $facet->field;
        // https://solr.apache.org/guide/solr/latest/query-guide/faceting.html#tagging-and-excluding-filters
        if ($facet->excludeFilter !== null) {
            $field = '{!ex=' . $facet->excludeFilter . '}' . $field;
        }
        /** @var Field $solariumFacet */
        $solariumFacet = $facetSet->createFacetField($facet->key);
        $solariumFacet
            ->setField($field)
            ->setTerms($facet->terms);
    }

    /**
     * https://solarium.readthedocs.io/en/stable/queries/select-query/building-a-select-query/components/facetset-component/facet-query/
     */
    private function addFacetQueryToSolrQuery(
        SolrSelectQuery $solrQuery,
        FacetQuery $facet
    ): void {
        $facetSet = $solrQuery->getFacetSet();
        $facetSet->createFacetQuery($facet->key)
            ->setQuery($facet->query);
    }

    /**
     * https://solarium.readthedocs.io/en/stable/queries/select-query/building-a-select-query/components/facetset-component/facet-multiquery/
     */
    private function addFacetMultiQueryToSolrQuery(
        SolrSelectQuery $solrQuery,
        FacetMultiQuery $facet
    ): void {
        $facetSet = $solrQuery->getFacetSet();
        $solrFacet = $facetSet->createFacetMultiQuery($facet->key);
        foreach ($facet->queries as $facetQuery) {
            $solrFacet->createQuery(
                $facetQuery->key,
                $facetQuery->query
            );
        }
    }

    private function buildResult(
        SelectQuery $query,
        SelectResult $result
    ): SearchResult {

        $resourceList = $this->resultToResourceResolver
            ->loadResourceList($result);
        $facetGroupList = $this->buildFacetGroupList($query, $result);

        return new SearchResult(
            $result->getNumFound() ?? 0,
            $query->limit,
            $query->offset,
            $resourceList,
            $facetGroupList,
            $result->getQueryTime() ?? 0
        );
    }

    /**
     * @return FacetGroup[]
     */
    private function buildFacetGroupList(
        SelectQuery $query,
        SelectResult $result
    ): array {

        $facetSet = $result->getFacetSet();
        if ($facetSet === null) {
            return [];
        }

        $facetGroupList = [];
        foreach ($query->facets as $facet) {
            /** @var ?\Solarium\Component\Result\Facet\Field $resultFacet */
            $resultFacet = $facetSet->getFacet($facet->key);
            if ($resultFacet === null) {
                continue;
            }
            $facetGroupList[] = $this->buildFacetGroup(
                $facet->key,
                $resultFacet
            );
        }
        return $facetGroupList;
    }

    private function buildFacetGroup(
        string $key,
        \Solarium\Component\Result\Facet\Field $solrFacet
    ): FacetGroup {
        $facetList = [];
        foreach ($solrFacet as $value => $count) {
            if (!is_int($count)) {
                throw new InvalidArgumentException(
                    'facet count should be a int: ' . $count
                );
            }
            $facetList[] = new Facet((string)$value, $count);
        }
        return new FacetGroup($key, $facetList);
    }
}
