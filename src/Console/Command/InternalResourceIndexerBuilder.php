<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Resource\Loader\SiteKitLoader;
use Atoolo\Resource\Loader\SiteKitNavigationHierarchyLoader;
use Atoolo\Search\Console\Command\Io\IndexerProgressBar;
use Atoolo\Search\Service\Indexer\ContentCollector;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexingAborter;
use Atoolo\Search\Service\Indexer\InternalResourceIndexer;
use Atoolo\Search\Service\Indexer\LocationFinder;
use Atoolo\Search\Service\Indexer\SiteKit\ContentMatcher;
use Atoolo\Search\Service\Indexer\SiteKit\DefaultSchema2xDocumentEnricher;
use Atoolo\Search\Service\Indexer\SiteKit\HeadlineMatcher;
use Atoolo\Search\Service\Indexer\SiteKit\RichtTextMatcher;
use Atoolo\Search\Service\Indexer\SiteKit\SubDirTranslationSplitter;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Atoolo\Search\Service\SolrParameterClientFactory;

class InternalResourceIndexerBuilder
{
    private string $resourceDir;
    /**
     * phpcs:ignore
     * @var iterable<DocumentEnricher<IndexDocument>>
     */
    private iterable $documentEnricherList;
    private IndexerProgressBar $progressBar;
    private string $solrConnectionUrl;

    public function __construct(
        private readonly ResourceBaseLocatorBuilder $resourceBaseLocatorBuilder
    ) {
    }
    public function resourceDir(
        string $resourceDir
    ): InternalResourceIndexerBuilder {
        $this->resourceDir = $resourceDir;
        return $this;
    }

    /**
     * phpcs:ignore
     * @param iterable<DocumentEnricher<IndexDocument>> $documentEnricherList
     */
    public function documentEnricherList(
        iterable $documentEnricherList
    ): InternalResourceIndexerBuilder {
        $this->documentEnricherList = $documentEnricherList;
        return $this;
    }

    public function progressBar(
        IndexerProgressBar $progressBar
    ): InternalResourceIndexerBuilder {
        $this->progressBar = $progressBar;
        return $this;
    }

    public function solrConnectionUrl(
        string $solrConnectionUrl
    ): InternalResourceIndexerBuilder {
        $this->solrConnectionUrl = $solrConnectionUrl;
        return $this;
    }

    public function build(): InternalResourceIndexer
    {
        $resourceBaseLocator = $this->resourceBaseLocatorBuilder->build(
            $this->resourceDir
        );
        $finder = new LocationFinder($resourceBaseLocator);
        $resourceLoader = new SiteKitLoader($resourceBaseLocator);
        $navigationLoader = new SiteKitNavigationHierarchyLoader(
            $resourceLoader
        );

        /** @var iterable<ContentMatcher> $matcher */
        $matcher = [
            new HeadlineMatcher(),
            new RichtTextMatcher(),
        ];
        $contentCollector = new ContentCollector($matcher);

        $schema21 = new DefaultSchema2xDocumentEnricher(
            $navigationLoader,
            $contentCollector
        );

        /** @var array<DocumentEnricher<IndexDocument>> $documentEnricherList */
        $documentEnricherList = [$schema21];
        foreach ($this->documentEnricherList as $enricher) {
            $documentEnricherList[] = $enricher;
        }
        /** @var string[] $url */
        $url = parse_url($this->solrConnectionUrl);

        $clientFactory = new SolrParameterClientFactory(
            $url['scheme'],
            $url['host'],
            (int)($url['port'] ?? ($url['scheme'] === 'https' ? 443 : 8382)),
            $url['path'] ?? '',
            null,
            0
        );

        $solrIndexService = new SolrIndexService($clientFactory);

        $translationSplitter = new SubDirTranslationSplitter();

        $aborter = new IndexingAborter('.');

        return new InternalResourceIndexer(
            $documentEnricherList,
            $this->progressBar,
            $finder,
            $resourceLoader,
            $translationSplitter,
            $solrIndexService,
            $aborter,
            'internal'
        );
    }
}
