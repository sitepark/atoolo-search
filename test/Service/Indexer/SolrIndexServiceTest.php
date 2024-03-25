<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Service\Indexer\SolrIndexService;
use Atoolo\Search\Service\SolrClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\QueryType\Server\CoreAdmin\Result\Result as CoreAdminResult;
use Solarium\QueryType\Server\CoreAdmin\Result\StatusResult;

#[CoversClass(SolrIndexService::class)]
class SolrIndexServiceTest extends TestCase
{
    private SolrIndexService $indexService;
    private SolrClientFactory&MockObject $factory;
    private Client&MockObject $client;

    public function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->factory = $this->createMock(SolrClientFactory::class);
        $this->factory->method('create')->willReturn($this->client);
        $this->indexService = new SolrIndexService($this->factory);
    }
    public function testUpdater(): void
    {
        $this->client->expects($this->once())->method('createUpdate');
        $this->indexService->updater('test');
    }

    public function testDeleteExcludingProcessId(): void
    {
        $this->client->expects($this->once())->method('createUpdate');
        $this->indexService->deleteExcludingProcessId('test', 'test', 'test');
    }

    public function testDeleteByIdList(): void
    {
        $this->client->expects($this->once())->method('createUpdate');
        $this->indexService->deleteByIdList('test', 'test', ['test']);
    }

    public function testByQuery(): void
    {
        $this->client->expects($this->once())->method('createUpdate');
        $this->indexService->deleteByQuery('test', 'test');
    }

    public function testCommit(): void
    {
        $this->client->expects($this->once())->method('update');
        $this->indexService->commit('test');
    }

    public function testGetAvailableCores(): void
    {
        $statusResult = $this->createStub(StatusResult::class);
        $statusResult->method('getCoreName')->willReturn('test');
        $response = $this->createStub(CoreAdminResult::class);
        $response->method('getStatusResults')->willReturn([$statusResult]);
        $this->client->method('coreAdmin')->willReturn($response);

        $cores = $this->indexService->getAvailableIndices();

        $this->assertEquals(['test'], $cores, 'Cores should be returned');
    }
}
