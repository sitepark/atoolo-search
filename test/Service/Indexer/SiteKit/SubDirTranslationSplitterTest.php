<?php

namespace Atoolo\Search\Test\Service\Indexer\SiteKit;

use Atoolo\Search\Service\Indexer\SiteKit\SubDirTranslationSplitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SubDirTranslationSplitter::class)]
class SubDirTranslationSplitterTest extends TestCase
{
    public function testBases(): void
    {
        $splitter = new SubDirTranslationSplitter();
        $result = $splitter->split(['/a/b.php']);

        $this->assertEquals(
            ['/a/b.php'],
            $result->getBases(),
            'unexpected bases'
        );
    }

    public function testLocales(): void
    {
        $splitter = new SubDirTranslationSplitter();
        $result = $splitter->split([
            '/a/b.php',
            '/a/b.php.translations/it_IT.php',
            '/a/b.php.translations/en_US.php'
        ]);

        $this->assertEquals(
            ['en_US', 'it_IT'],
            $result->getLocales(),
            'unexpected locales'
        );
    }

    public function testGetTranlsations(): void
    {
        $splitter = new SubDirTranslationSplitter();
        $result = $splitter->split([
            '/a/b.php',
            '/a/b.php.translations/it_IT.php',
            '/a/b.php.translations/en_US.php',
            '/c/d.php',
            '/c/d.php.translations/it_IT.php',
            '/c/d.php.translations/en_US.php'
        ]);

        $translations = $result->getTranslations('it_IT');

        $expected = [
            '/a/b.php.translations/it_IT.php',
            '/c/d.php.translations/it_IT.php'
        ];

        $this->assertEquals(
            $expected,
            $translations,
            'unexpected translations'
        );
    }
}
