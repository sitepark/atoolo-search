<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer\SiteKit;

/**
 * The `ContentMatcher` interface is implemented in order to extract from the
 * content structure of resources the content that is relevant for the `content`
 * field of the search index.
 */
interface ContentMatcher
{
    /**
     * @param string[] $path Contains all array-keys of the nested data
     * structure that lead to the transferred value. E.g. ['a', 'b', 'c']
     * for the following structure.
     * ```
     * [
     *   'a' => [
     *     'b' => [
     *       'c' => [
     *         ...
     *       ]
     *     ]
     *   ]
     * ]
     * ```
     * @param array<mixed, mixed> $value Value within a data structure
     *        that is to be checked.
     * @return string|false The extracted content or `false` if the
     *         content is not relevant for the search index.
     */
    public function match(array $path, array $value): string|false;
}
