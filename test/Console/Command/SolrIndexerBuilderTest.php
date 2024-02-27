<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Search\Console\Command\Io\IndexerProgressBar;
use Atoolo\Search\Console\Command\ResourceBaseLocatorBuilder;
use Atoolo\Search\Console\Command\SolrIndexerBuilder;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SolrIndexerBuilder::class)]
class SolrIndexerBuilderTest extends TestCase
{
    public function testBuild(): void
    {

        $builder = new SolrIndexerBuilder(
            $this->createStub(ResourceBaseLocatorBuilder::class)
        );
        $builder
            ->resourceDir('test')
            ->documentEnricherList([$this->createStub(DocumentEnricher::class)])
            ->progressBar($this->createStub(IndexerProgressBar::class))
            ->solrConnectionUrl('http://localhost:8382');

        $this->expectNotToPerformAssertions();
        $builder->build();
    }
}
