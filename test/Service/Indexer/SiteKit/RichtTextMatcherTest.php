<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer\SiteKit;

use Atoolo\Search\Service\Indexer\SiteKit\RichtTextMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RichtTextMatcher::class)]
class RichtTextMatcherTest extends TestCase
{
    public function testMatcher(): void
    {
        $matcher = new RichtTextMatcher();

        $value = [
            "normalized" => true,
            "modelType" => "html.richText",
            "text" => "<p>Ein Text</p>"
        ];

        $content = $matcher->match([], $value);

        $this->assertEquals('Ein Text', $content, 'unexpected content');
    }

    public function testMatcherNotMatchedInvalidType(): void
    {
        $matcher = new RichtTextMatcher();

        $value = [
            "normalized" => true,
            "modelType" => "html.richTextX",
            "text" => "<p>Ein Text</p>"
        ];

        $content = $matcher->match([], $value);

        $this->assertEmpty(
            $content,
            'should not find any content'
        );
    }

    public function testMatcherNotMatchedTextMissing(): void
    {
        $matcher = new RichtTextMatcher();

        $value = [
            "normalized" => true,
            "modelType" => "html.richText",
            "textX" => "<p>Ein Text</p>"
        ];

        $content = $matcher->match([], $value);

        $this->assertEmpty(
            $content,
            'should not find any content'
        );
    }
}
