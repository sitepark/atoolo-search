<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Solarium\Client;
use Solarium\QueryType\Update\Query\Document;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;
use Solarium\QueryType\Update\Result as UpdateResult;

class SolrIndexUpdater
{
    /**
     * @var Document[]
     */
    private array $documents = [];

    public function __construct(
        private readonly Client $client,
        private readonly UpdateQuery $update
    ) {
    }

    public function createDocument(): Document
    {
        /** @var Document $doc */
        $doc = $this->update->createDocument();
        return $doc;
    }

    public function addDocument(Document $document): void
    {
        $this->documents[] = $document;
    }

    public function update(): UpdateResult
    {
        $this->update->addDocuments($this->documents);
        $this->documents = [];
        return $this->client->update($this->update);
    }
}
