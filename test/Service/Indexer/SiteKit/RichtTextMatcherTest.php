<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer\SiteKit;

use Atoolo\Search\Service\Indexer\SiteKit\RichtTextMatcher;
use PHPUnit\Framework\TestCase;

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
}
