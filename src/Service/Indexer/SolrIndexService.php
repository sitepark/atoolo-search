<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Search\Service\SolrClientFactory;

class SolrIndexService
{
    public function __construct(
        private readonly SolrClientFactory $clientFactory
    ) {
    }

    public function updater(string $core): SolrIndexUpdater
    {
        $client = $this->clientFactory->create($core);
        $update = $client->createUpdate();
        $update->setDocumentClass(IndexSchema2xDocument::class);

        return new SolrIndexUpdater($client, $update);
    }

    public function deleteExcludingProcessId(
        string $core,
        string $source,
        string $processId
    ): void {
        $this->deleteByQuery(
            $core,
            '-crawl_process_id:' . $processId . ' AND ' .
            ' sp_source:' . $source
        );
    }

    /**
     * @param string[] $idList
     */
    public function deleteByIdList(
        string $core,
        string $source,
        array $idList
    ): void {
        $this->deleteByQuery(
            $core,
            'sp_id:(' . implode(' ', $idList) . ') AND ' .
            'sp_source:' . $source
        );
    }

    public function deleteByQuery(string $core, string $query): void
    {
        $client = $this->clientFactory->create($core);
        $update = $client->createUpdate();
        $update->addDeleteQuery($query);
        $client->update($update);
    }

    public function commit(string $core): void
    {
        $client = $this->clientFactory->create($core);
        $update = $client->createUpdate();
        $update->addCommit();
        $update->addOptimize();
        $client->update($update);
    }

    /**
     * @return string[]
     */
    public function getAvailableIndices(): array
    {
        $client = $this->clientFactory->create('');
        $coreAdminQuery = $client->createCoreAdmin();
        $statusAction = $coreAdminQuery->createStatus();
        $coreAdminQuery->setAction($statusAction);

        $availableIndexes = [];
        $response = $client->coreAdmin($coreAdminQuery);
        $statusResults = $response->getStatusResults() ?? [];
        foreach ($statusResults as $statusResult) {
            $availableIndexes[] = $statusResult->getCoreName();
        }

        return $availableIndexes;
    }
}
