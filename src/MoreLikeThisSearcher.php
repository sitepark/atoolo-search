<?php

declare(strict_types=1);

namespace Atoolo\Search;

use Atoolo\Search\Dto\Search\Query\MoreLikeThisQuery;
use Atoolo\Search\Dto\Search\Result\SearchResult;

/**
 * The service interface for a "More-Like-This" search.
 *
 * A "More-Like-This" search is a technique in which a source document or item
 * is used as a reference point to find similar documents in the search index.
 * It is based on extracting characteristics from the source object and
 * searching for other objects that have similar characteristics in order to
 * present relevant results to the user.
 *
 * The reference point is specified via the location of a resource.
 */
interface MoreLikeThisSearcher
{
    public function moreLikeThis(
        MoreLikeThisQuery $query
    ): SearchResult;
}
