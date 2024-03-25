<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer\SiteKit;

use Atoolo\Search\Service\Indexer\TranslationSplitter;
use Atoolo\Search\Service\Indexer\TranslationSplitterResult;

/**
 * The task of the SubDirTranslationSplitter is to receive a list of resource
 * paths and use the file path to check for which language the resource
 * must be indexed, as each language is stored in a separate index.
 */
class SubDirTranslationSplitter implements TranslationSplitter
{
    /**
     * @param string[] $pathList
     */
    public function split(array $pathList): TranslationSplitterResult
    {
        $bases = [];
        $translations = [];
        foreach ($pathList as $path) {
            $locale = $this->extractLocaleFromPath($path);
            if ($locale === 'default') {
                $bases[] = $path;
                continue;
            }
            if (!isset($translations[$locale])) {
                $translations[$locale] = [];
            }
            $translations[$locale][] = $path;
        }

        return new TranslationSplitterResult($bases, $translations);
    }

    private function extractLocaleFromPath(string $path): string
    {
        $filename = basename($path);
        $parentDirName = basename(dirname($path));

        if (!str_ends_with($parentDirName, '.php.translations')) {
            return 'default';
        }

        return basename($filename, '.php');
    }
}
