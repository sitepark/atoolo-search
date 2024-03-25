<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Search\SiteKit;

use Atoolo\Search\Service\Search\SolrQueryModifier;
use Solarium\QueryType\Select\Query\Query as SelectQuery;

/**
 * Set solr boost definitions for certain fields.
 * This influences the relevance of the fields in the search.
 *
 * The values defined here have been developed through experience in
 * many projects.
 */
class DefaultBoostModifier implements SolrQueryModifier
{
    public function modify(SelectQuery $query): SelectQuery
    {
        $edismax = $query->getEDisMax();
        $edismax->setQueryFields(implode(' ', [
            'sp_title^1.4',
            'keywords^1.2',
            'description^1.0',
            'title^1.0',
            'url^0.9',
            'content^0.8'
        ]));
        $edismax->setPhraseFields(implode(' ', [
            'sp_title^1.5',
            'description^1',
            'content^0.8'
        ]));
        $edismax->setBoostQuery('sp_objecttype:searchTip^100');

        return $query;
    }
}
