<?php

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Service\Indexer\TranslationSplitterResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslationSplitterResult::class)]
class TranslationSplitterResultTest extends TestCase
{
    public function testGetBases(): void
    {
        $result = new TranslationSplitterResult(['/a/b.php'], []);
        $this->assertEquals(
            ['/a/b.php'],
            $result->getBases(),
            'unexpected bases'
        );
    }

    public function testLocales(): void
    {
        $result = new TranslationSplitterResult(
            [],
            [
                'it_IT' => ['/a/b.php.translations/it_IT.php'],
                'en_US' => ['/a/b.php.translations/en_US.php']
            ]
        );

        $this->assertEquals(
            ['en_US', 'it_IT'],
            $result->getLocales(),
            'unexpected locales'
        );
    }

    public function testGetTranslations(): void
    {
        $result = new TranslationSplitterResult(
            [],
            [
                'it_IT' => [
                    '/a/b.php.translations/it_IT.php',
                    '/c/d.php.translations/it_IT.php',
                ],
                'en_US' => [
                    '/a/b.php.translations/en_US.php',
                    '/a/b.php.translations/en_US.php',
                ]
            ]
        );

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
    public function testGetMissingTranslations(): void
    {
        $result = new TranslationSplitterResult([], []);

        $this->assertEquals(
            [],
            $result->getTranslations('en_US'),
            'empty array expected'
        );
    }
}
