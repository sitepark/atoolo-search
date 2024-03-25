<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Search\Service\Indexer\SiteKit\ContentMatcher;

class ContentCollector
{
    /**
     * @param iterable<ContentMatcher> $matchers
     */
    public function __construct(private readonly iterable $matchers)
    {
    }

    /**
    * @param array<mixed,mixed> $data
     */
    public function collect(array $data): string
    {
        $content = $this->walk([], $data);
        return implode(' ', $content);
    }

    /**
     * @param string[] $path
     * @param array<mixed,mixed> $data
     * @return string[]
     */
    private function walk(array $path, array $data): array
    {
        $contentCollections = [];
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (is_string($key)) {
                $path[] = $key;
            }

            $matcherContent = [];
            foreach ($this->matchers as $matcher) {
                $content = $matcher->match($path, $value);
                if (!is_string($content)) {
                    continue;
                }
                $matcherContent[] = $content;
            }
            $contentCollections[] = $matcherContent;
            $contentCollections[] = $this->walk($path, $value);

            if (is_string($key)) {
                array_pop($path);
            }
        }

        return array_merge(...$contentCollections);
    }
}
