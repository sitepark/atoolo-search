<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexSchema2xDocument::class)]
class IndexSchema2xDocumentTest extends TestCase
{
    public function testGetFields(): void
    {
        $doc = new IndexSchema2xDocument();
        $doc->sp_id = '123';

        $this->assertEquals(
            ['sp_id' => '123'],
            $doc->getFields(),
            'unexpected fields'
        );
    }

    public function testSetMetaString(): void
    {
        $doc = new IndexSchema2xDocument();
        $doc->setMetaString('myname', 'myvalue');

        $this->assertEquals(
            ['sp_meta_string_myname' => 'myvalue'],
            $doc->getFields(),
            'unexpected meta fields'
        );
    }

    public function testSetMetaText(): void
    {
        $doc = new IndexSchema2xDocument();
        $doc->setMetaText('myname', 'myvalue');

        $this->assertEquals(
            ['sp_meta_text_myname' => 'myvalue'],
            $doc->getFields(),
            'unexpected meta fields'
        );
    }

    public function testSetMetaBool(): void
    {
        $doc = new IndexSchema2xDocument();
        $doc->setMetaBool('myname', true);

        $this->assertEquals(
            ['sp_meta_bool_myname' => true],
            $doc->getFields(),
            'unexpected meta fields'
        );
    }
}
