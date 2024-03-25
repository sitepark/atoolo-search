<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Resource\ResourceLoader;

class InternalResourceIndexerFactory
{
    /**
     * @param iterable<DocumentEnricher<IndexDocument>> $documentEnricherList
     */
    public function __construct(
        private readonly iterable $documentEnricherList,
        private readonly LocationFinder $finder,
        private readonly ResourceLoader $resourceLoader,
        private readonly TranslationSplitter $translationSplitter,
        private readonly SolrIndexService $solrService,
        private readonly IndexingAborter $aborter,
        private readonly string $source
    ) {
    }

    public function create(
        IndexerProgressHandler $progressHandler
    ): InternalResourceIndexer {
        return new InternalResourceIndexer(
            $this->documentEnricherList,
            $progressHandler,
            $this->finder,
            $this->resourceLoader,
            $this->translationSplitter,
            $this->solrService,
            $this->aborter,
            $this->source
        );
    }
}
