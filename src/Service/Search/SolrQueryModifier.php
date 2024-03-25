<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Search;

use Solarium\QueryType\Select\Query\Query as SelectQuery;

/**
 * SolrQueryModifiers can be passed to the search service to make additional
 * modifications to the search query. This is used, for example, to set the
 * same boosting for all queries.
 */
interface SolrQueryModifier
{
    public function modify(SelectQuery $query): SelectQuery;
}
