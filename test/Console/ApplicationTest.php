<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console;

use Atoolo\Resource\ResourceChannel;
use Atoolo\Search\Console\Application;
use Atoolo\Search\Console\Command\Indexer;
use Atoolo\Search\Console\Command\Io\IndexerProgressBar;
use Atoolo\Search\Service\Indexer\IndexerCollection;
use Atoolo\Search\Service\Indexer\InternalResourceIndexer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(Application::class)]
class ApplicationTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $resourceChannel = $this->createStub(
            ResourceChannel::class,
        );
        $indexer = $this->createStub(
            InternalResourceIndexer::class,
        );
        $indexers = new IndexerCollection([$indexer]);
        $progressBar = $this->createStub(IndexerProgressBar::class);
        $application = new Application([
            new Indexer($resourceChannel, $progressBar, $indexers),
        ]);
        $command = $application->get('search:indexer');
        $this->assertInstanceOf(
            Indexer::class,
            $command,
            'unexpected indexer command',
        );
    }
}
