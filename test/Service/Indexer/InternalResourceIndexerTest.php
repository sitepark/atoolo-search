<?php

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Search\Dto\Indexer\IndexerParameter;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexerProgressHandler;
use Atoolo\Search\Service\Indexer\IndexingAborter;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Atoolo\Search\Service\Indexer\InternalResourceIndexer;
use Atoolo\Search\Service\Indexer\LocationFinder;
use Atoolo\Search\Service\Indexer\SiteKit\SubDirTranslationSplitter;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Atoolo\Search\Service\Indexer\SolrIndexUpdater;
use Atoolo\Search\Service\Indexer\TranslationSplitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Update\Result as UpdateResult;

#[CoversClass(InternalResourceIndexer::class)]
class InternalResourceIndexerTest extends TestCase
{
    private array $availableIndexes = ['test', 'test-en_US'];

    private ResourceLoader&Stub $resourceLoader;

    private IndexerProgressHandler&MockObject $indexerProgressHandler;

    private InternalResourceIndexer $indexer;

    private SolrIndexService $solrIndexService;

    private LocationFinder&MockObject $finder;

    private SolrIndexUpdater&MockObject $updater;

    private UpdateResult&Stub $updateResult;

    private IndexingAborter&MockObject $aborter;

    private DocumentEnricher&MockObject $documentEnricher;

    private TranslationSplitter $translationSplitter;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->indexerProgressHandler = $this->createMock(
            IndexerProgressHandler::class
        );
        $this->finder = $this->createMock(LocationFinder::class);
        $this->documentEnricher = $this->createMock(DocumentEnricher::class);
        $this->documentEnricher
            ->method('enrichDocument')
            ->willReturnCallback(function ($resource, $doc) {
                return $doc;
            });
        $this->translationSplitter = new SubDirTranslationSplitter();
        $this->resourceLoader = $this->createStub(ResourceLoader::class);
        $this->resourceLoader->method('load')
            ->willReturnCallback(function ($path) {
                $resource = $this->createStub(Resource::class);
                $resource->method('getLocation')
                    ->willReturn($path);
                return $resource;
            });
        $this->solrIndexService = $this->createMock(SolrIndexService::class);
        $this->updateResult = $this->createStub(UpdateResult::class);
        $this->updater = $this->createMock(SolrIndexUpdater::class);
        $this->updater->method('update')->willReturn($this->updateResult);
        $this->updater->method('createDocument')->willReturn(
            new IndexSchema2xDocument()
        );
        $this->solrIndexService->method('getAvailableIndices')
            ->willReturnCallback(function () {
                return $this->availableIndexes;
            });
        $this->solrIndexService->method('updater')
            ->willReturn($this->updater);
        $this->aborter =  $this->createMock(IndexingAborter::class);

        $this->indexer = new InternalResourceIndexer(
            [ $this->documentEnricher ],
            $this->indexerProgressHandler,
            $this->finder,
            $this->resourceLoader,
            $this->translationSplitter,
            $this->solrIndexService,
            $this->aborter,
            'test'
        );
    }

    public function testAbort(): void
    {
        $this->aborter->expects($this->once())
            ->method('requestAbortion')
            ->with('test');

        $this->indexer->abort('test');
    }

    public function testRemove(): void
    {
        $this->solrIndexService->expects($this->once())
            ->method('deleteByIdList');

        $this->indexer->remove('test', ['123']);
    }

    public function testRemoveEmpty(): void
    {
        $this->solrIndexService->expects($this->exactly(0))
            ->method('deleteByIdList');

        $this->indexer->remove('test', []);
    }

    public function testIndexAllWithChunks(): void
    {
        $this->finder->method('findAll')
            ->willReturn([
                '/a/b.php',
                '/a/b.php.translations/en_US.php',
                '/a/c.php',
                '/a/c.php.translations/fr_FR.php',
                '/a/d.php',
                '/a/e.php',
                '/a/f.php',
                '/a/g.php',
                '/a/h.php',
                '/a/i.php',
                '/a/j.php',
                '/a/k.php',
                '/a/l.php',
                '/a/error.php'
            ]);

        $this->updateResult->method('getStatus')
            ->willReturn(0);

        $this->documentEnricher->method('isIndexable')
            ->willReturn(true);

        $this->documentEnricher
            ->method('enrichDocument')
            ->willReturnCallback(function ($resource, $doc) {
                if ($resource->getLocation() === '/a/error.php') {
                    throw new \Exception('test');
                }
                return $doc;
            });

        $this->updater->expects($this->exactly(12))
            ->method('addDocument');

        $this->updater->expects($this->exactly(3))
            ->method('update');

        $parameter = new IndexerParameter(
            'test',
            10,
            10
        );

        $this->indexerProgressHandler->expects($this->exactly(2))
            ->method('error');

        $this->indexer->index($parameter);
    }

    public function testIndexSkipResource(): void
    {
        $this->finder->method('findAll')
            ->willReturn([
                '/a/b.php',
                '/a/c.php'
            ]);

        $this->updateResult->method('getStatus')
            ->willReturn(0);

        $this->documentEnricher->method('isIndexable')
            ->willReturnCallback(function (Resource $resource) {
                $location = $resource->getLocation();
                return ($location !== '/a/b.php');
            });

        $this->updater->expects($this->exactly(1))
            ->method('addDocument');

        $this->updater->expects($this->exactly(1))
            ->method('update');

        $parameter = new IndexerParameter(
            'test',
            10,
            10
        );

        $this->indexer->index($parameter);
    }

    public function testAborted(): void
    {
        $this->finder->method('findAll')
            ->willReturn([
                '/a/b.php',
                '/a/c.php'
            ]);

        $this->aborter->method('isAbortionRequested')
            ->willReturn(true);

        $this->aborter->expects($this->once())
            ->method('resetAbortionRequest');

        $this->indexerProgressHandler->expects($this->once())
            ->method('abort');

        $parameter = new IndexerParameter(
            'test',
            10,
            10
        );

        $this->indexer->index($parameter);
    }

    public function testWithUnsuccessfulStatus(): void
    {
        $this->finder->method('findAll')
            ->willReturn([
                '/a/b.php',
                '/a/c.php'
            ]);

        $this->updateResult->method('getStatus')
            ->willReturn(500);

        $this->documentEnricher->method('isIndexable')
            ->willReturn(true);

        $this->indexerProgressHandler->expects($this->once())
            ->method('error');

        $parameter = new IndexerParameter(
            'test',
            10,
            10
        );

        $this->indexer->index($parameter);
    }

    public function testWithInvalidResource(): void
    {
        $this->finder->method('findAll')
            ->willReturn([
                '/a/b.php',
            ]);

        $this->resourceLoader->method('load')
            ->willThrowException(new InvalidResourceException('/a/b.php'));

        $this->indexerProgressHandler->expects($this->once())
            ->method('error');

        $parameter = new IndexerParameter(
            'test',
            10,
            10
        );

        $this->indexer->index($parameter);
    }

    public function testEmptyStatus(): void
    {
        $this->finder->method('findAll')
            ->willReturn([]);

        $parameter = new IndexerParameter(
            'test',
            10,
            10
        );

        $status = $this->indexer->index($parameter);
        $this->assertEquals(0, $status->total, 'total should be 0');
    }

    public function testIndexPaths(): void
    {
        $this->finder->method('findPaths')
            ->willReturn([
                '/a/b.php',
                '/a/c.php'
            ]);

        $this->updateResult->method('getStatus')
            ->willReturn(0);

        $this->documentEnricher->method('isIndexable')
            ->willReturn(true);

        $this->updater->expects($this->exactly(2))
            ->method('addDocument');

        $this->updater->expects($this->exactly(1))
            ->method('update');

        $parameter = new IndexerParameter(
            'test',
            10,
            10,
            [
                '/a/b.php',
                '/a/c.php'
            ]
        );

        $this->indexer->index($parameter);
    }

    public function testIndexPathWithParameter(): void
    {
        $this->finder->expects($this->once())
            ->method('findPaths')
            ->with($this->equalTo(['?a=b']));

        $parameter = new IndexerParameter(
            'test',
            10,
            10,
            [
                '?a=b'
            ]
        );

        $this->indexer->index($parameter);
    }

    public function testIndexPathWithParameterAndPath(): void
    {
        $this->finder->expects($this->once())
            ->method('findPaths')
            ->with($this->equalTo(['/test.php']));

        $parameter = new IndexerParameter(
            'test',
            10,
            10,
            [
                '/test.php?a=b'
            ]
        );

        $this->indexer->index($parameter);
    }

    public function testIndexPathWithLocParameterAndPath(): void
    {
        $this->finder->expects($this->once())
            ->method('findPaths')
            ->with($this->equalTo(['/test.php.translations/en.php']));

        $parameter = new IndexerParameter(
            'test',
            10,
            10,
            [
                '/test.php?loc=en'
            ]
        );

        $this->indexer->index($parameter);
    }

    public function testWithoutAvailableIndexes(): void
    {

        $this->availableIndexes = [];
        $this->finder->method('findAll')
            ->willReturn([
                '/a/b.php',
                '/a/c.php',
                '/a/d.php',
                '/a/e.php',
                '/a/f.php',
                '/a/g.php',
                '/a/h.php',
                '/a/i.php',
                '/a/j.php',
                '/a/k.php',
                '/a/l.php'
            ]);

        $parameter = new IndexerParameter(
            'test',
            10,
            10
        );

        $this->indexerProgressHandler->expects($this->once())
            ->method('error');

        $this->indexer->index($parameter);
    }
}
