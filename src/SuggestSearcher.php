<?php

declare(strict_types=1);

namespace Atoolo\Search;

use Atoolo\Search\Dto\Search\Query\SuggestQuery;
use Atoolo\Search\Dto\Search\Result\SuggestResult;

/**
 * The service interface for a suggest search.
 *
 * A "suggest search" is a search function that automatically displays
 * suggestions or auto-completions to users as they enter search queries.
 */
interface SuggestSearcher
{
    public function suggest(SuggestQuery $query): SuggestResult;
}
