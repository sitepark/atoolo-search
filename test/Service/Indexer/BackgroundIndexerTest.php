<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Dto\Indexer\IndexerParameter;
use Atoolo\Search\Service\Indexer\BackgroundIndexer;
use Atoolo\Search\Service\Indexer\IndexerStatusStore;
use Atoolo\Search\Service\Indexer\InternalResourceIndexer;
use Atoolo\Search\Service\Indexer\InternalResourceIndexerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

#[CoversClass(BackgroundIndexer::class)]
class BackgroundIndexerTest extends TestCase
{
    private InternalResourceIndexer&MockObject $solrIndexer;
    private IndexerStatusStore&MockObject $statusStore;
    private BackgroundIndexer $indexer;

    public function setUp(): void
    {
        $this->solrIndexer = $this->createMock(InternalResourceIndexer::class);
        $solrIndexerFactory = $this->createStub(
            InternalResourceIndexerFactory::class
        );
        $solrIndexerFactory->method('create')
            ->willReturn($this->solrIndexer);
        $this->statusStore = $this->createMock(IndexerStatusStore::class);
        $this->indexer = new BackgroundIndexer(
            $solrIndexerFactory,
            $this->statusStore
        );
    }

    public function testRemove(): void
    {
        $this->solrIndexer->expects($this->once())
            ->method('remove');
        $this->indexer->remove('test', ['123']);
    }

    public function testAbort(): void
    {
        $this->solrIndexer->expects($this->once())
            ->method('abort');
        $this->indexer->abort('test');
    }

    public function testIndex(): void
    {
        $params = new IndexerParameter('test');
        $this->solrIndexer->expects($this->once())
            ->method('index');
        $this->indexer->index($params);
    }

    public function testIndexIfLocked(): void
    {
        $lockFactory = $this->createStub(LockFactory::class);
        $indexer = new BackgroundIndexer(
            $this->createStub(InternalResourceIndexerFactory::class),
            $this->statusStore,
            new NullLogger(),
            $lockFactory
        );

        $lock = $this->createStub(SharedLockInterface::class);
        $lock->method('acquire')
            ->willReturn(false);
        $lockFactory->method('createLock')
            ->willReturn($lock);

        $this->solrIndexer->expects($this->exactly(0))
            ->method('index');

        $params = new IndexerParameter('test');
        $indexer->index($params);
    }

    public function testGetStatus(): void
    {
        $params = new IndexerParameter('test');
        $this->statusStore->expects($this->once())
            ->method('load');
        $this->indexer->getStatus('test');
    }
}
