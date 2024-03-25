<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Search\Dto\Indexer\IndexerParameter;
use Atoolo\Search\Dto\Indexer\IndexerStatus;
use Atoolo\Search\Indexer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class BackgroundIndexer implements Indexer
{
    public function __construct(
        private readonly InternalResourceIndexerFactory $indexerFactory,
        private readonly IndexerStatusStore $statusStore,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly LockFactory $lockFactory = new LockFactory(
            new SemaphoreStore()
        )
    ) {
    }

    /**
     * @param string[] $idList
     */
    public function remove(string $index, array $idList): void
    {
        $this->getIndexer($index)->remove($index, $idList);
    }

    public function abort(string $index): void
    {
        $this->getIndexer($index)->abort($index);
    }

    public function index(IndexerParameter $parameter): IndexerStatus
    {
        $lock = $this->lockFactory->createLock($parameter->index);
        if (!$lock->acquire()) {
            return IndexerStatus::empty();
        }
        try {
            return $this->getIndexer($parameter->index)->index($parameter);
        } finally {
            $lock->release();
        }
    }

    /**
     * @throws ExceptionInterface
     */
    public function getStatus(string $index): IndexerStatus
    {
        return $this->statusStore->load($index);
    }

    private function getIndexer(string $index): InternalResourceIndexer
    {
        $progressHandler = new BackgroundIndexerProgressState(
            $index,
            $this->statusStore,
            $this->logger
        );

        return $this->indexerFactory->create($progressHandler);
    }
}
