<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Service\Indexer\ContentCollector;
use Atoolo\Search\Service\Indexer\SiteKit\ContentMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentCollector::class)]
class ContentCollectorTest extends TestCase
{
    public function testCollect(): void
    {
        $matcher = (new class implements ContentMatcher {
            public function match(array $path, array $value): string|false
            {
                $modelType = $value['modelType'] ?? false;
                if ($modelType !== 'html.richText') {
                    return false;
                }

                $text = $value['text'];
                return is_string($text) ? $text : false;
            }
        });
        $collector = new ContentCollector([$matcher]);

        $data = [
            "items" => [
                [
                    "model" => [
                        "richText" => [
                            "normalized" => true,
                            "modelType" => "html.richText",
                            "text" => "<p>Ein Text</p>"
                        ]
                    ]
                ]
            ]
        ];
        $content = $collector->collect($data);

        $this->assertEquals('<p>Ein Text</p>', $content, 'unexpected content');
    }
}
