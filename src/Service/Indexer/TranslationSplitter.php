<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

/**
 * The `TranslationSplitter` interface is implemented in order to
 * group a list of paths in the respective languages.
 */
interface TranslationSplitter
{
    /**
     * @param string[] $pathList
     */
    public function split(array $pathList): TranslationSplitterResult;
}
