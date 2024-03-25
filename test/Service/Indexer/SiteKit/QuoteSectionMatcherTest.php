<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer\SiteKit;

use Atoolo\Search\Service\Indexer\SiteKit\QuoteSectionMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QuoteSectionMatcher::class)]
class QuoteSectionMatcherTest extends TestCase
{
    public function testMatcher(): void
    {
        $matcher = new QuoteSectionMatcher();

        $value = [
            "type" => "quote",
            "model" => [
                "quote" => "Quote-Text",
                "citation" => "Citation"
            ]
        ];

        $content = $matcher->match(['items'], $value);

        $this->assertEquals(
            'Quote-Text Citation',
            $content,
            'unexpected quote text'
        );
    }
    public function testMatcherNoMatchPathToShort(): void
    {
        $matcher = new QuoteSectionMatcher();

        $value = [
            "type" => "quote",
            "model" => [
                "quote" => "Quote-Text",
                "citation" => "Citation"
            ]
        ];

        $content = $matcher->match([], $value);

        $this->assertEmpty(
            $content,
            'should not find any content'
        );
    }

    public function testMatcherNoMatchNoItems(): void
    {
        $matcher = new QuoteSectionMatcher();

        $value = [
            "type" => "quote",
            "model" => [
                "quote" => "Quote-Text",
                "citation" => "Citation"
            ]
        ];

        $content = $matcher->match(['itemsX'], $value);

        $this->assertEmpty(
            $content,
            'should not find any content'
        );
    }

    public function testMatcherNoMatchInvalidType(): void
    {
        $matcher = new QuoteSectionMatcher();

        $value = [
            "type" => "quoteX",
            "model" => [
                "quote" => "Quote-Text",
                "citation" => "Citation"
            ]
        ];

        $content = $matcher->match(['items'], $value);

        $this->assertEmpty(
            $content,
            'should not find any content'
        );
    }

    public function testMatcherNoMatchMissingModel(): void
    {
        $matcher = new QuoteSectionMatcher();

        $value = [
            "type" => "quote",
            "modelX" => [
                "quote" => "Quote-Text",
                "citation" => "Citation"
            ]
        ];

        $content = $matcher->match(['items'], $value);

        $this->assertEmpty(
            $content,
            'should not find any content'
        );
    }
}
