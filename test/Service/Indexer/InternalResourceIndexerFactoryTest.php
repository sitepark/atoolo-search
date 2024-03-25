<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Resource\ResourceLoader;
use Atoolo\Search\Service\Indexer\IndexerProgressHandler;
use Atoolo\Search\Service\Indexer\IndexingAborter;
use Atoolo\Search\Service\Indexer\InternalResourceIndexerFactory;
use Atoolo\Search\Service\Indexer\LocationFinder;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Atoolo\Search\Service\Indexer\TranslationSplitter;
use PHPUnit\Framework\TestCase;

class InternalResourceIndexerFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new InternalResourceIndexerFactory(
            [],
            $this->createStub(LocationFinder::class),
            $this->createStub(ResourceLoader::class),
            $this->createStub(TranslationSplitter::class),
            $this->createStub(SolrIndexService::class),
            $this->createStub(IndexingAborter::class),
            ''
        );

        $this->expectNotToPerformAssertions();
        $factory->create(
            $this->createStub(IndexerProgressHandler::class)
        );
    }
}
