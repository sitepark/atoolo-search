<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer\SiteKit;

class HeadlineMatcher implements ContentMatcher
{
    /**
     * @inheritDoc
     */
    public function match(array $path, array $value): string|false
    {
        $len = count($path);
        if ($len < 2) {
            return false;
        }

        if (
            $path[$len - 2] !== 'items' ||
            $path[$len - 1] !== 'model'
        ) {
            return false;
        }

        $headline = $value['headline'] ?? false;
        return is_string($headline) ? $headline : false;
    }
}
