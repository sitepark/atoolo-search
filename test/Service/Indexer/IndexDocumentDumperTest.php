<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Resource\ResourceLoader;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocumentDumper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexDocumentDumper::class)]
class IndexDocumentDumperTest extends TestCase
{
    public function testDump(): void
    {
        $resourceLoader = $this->createStub(ResourceLoader::class);
        $documentEnricher = $this->createStub(DocumentEnricher::class);
        $documentEnricher->method('enrichDocument')
            ->willReturnCallback(function ($resource, $doc) {
                $doc->sp_id = '123';
                return $doc;
            });
        $dumper = new IndexDocumentDumper(
            $resourceLoader,
            [$documentEnricher]
        );

        $dump = $dumper->dump(['/test.php']);

        $this->assertEquals(
            [['sp_id' => '123']],
            $dump,
            'unexpected dump'
        );
    }
}
