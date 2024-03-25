<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Atoolo\Search\Service\Indexer\SolrIndexUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;

#[CoversClass(SolrIndexUpdater::class)]
class SolrIndexUpdaterTest extends TestCase
{
    private SolrIndexUpdater $updater;

    private Client&MockObject $client;
    private UpdateQuery&MockObject $updateQuery;


    public function setUp(): void
    {
        $this->updateQuery = $this->createMock(UpdateQuery::class);
        $this->updateQuery->method('createDocument')->willReturn(
            new IndexSchema2xDocument()
        );
        $this->client = $this->createMock(Client::class);
        $this->updater = new SolrIndexUpdater(
            $this->client,
            $this->updateQuery
        );
    }

    public function testCreateDocument(): void
    {
        $this->updateQuery->expects($this->once())->method('createDocument');
        $this->updater->createDocument();
    }

    public function testAddAndUpdate(): void
    {
        $doc = $this->updater->createDocument();
        $this->updater->addDocument($doc);
        $this->updateQuery->expects($this->once())
            ->method('addDocuments')
            ->with([$doc]);
        $this->updater->update();
    }
}
