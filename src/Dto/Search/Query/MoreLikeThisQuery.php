<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query;

use Atoolo\Search\Dto\Search\Query\Filter\Filter;

/**
 * MoreLikeThis is a function in search technologies that finds similar
 * documents or content to a given document or query. It analyzes the
 * properties of the reference document, such as keywords or structure,
 * to identify other documents with similar characteristics in the
 * database or search index.
 *
 * @codeCoverageIgnore
 */
class MoreLikeThisQuery
{
    /**
     * @param string $index name of the index to use
     * @param Filter[] $filter
     * @param string[] $fields The fields specified here must be part of the
     * index schema and determine which fields are relevant for determining
     * which entries are similar.
     */
    public function __construct(
        public readonly string $index,
        public readonly string $location,
        public readonly array $filter = [],
        public readonly int $limit = 5,
        public readonly array $fields = ['description', 'content']
    ) {
    }
}
