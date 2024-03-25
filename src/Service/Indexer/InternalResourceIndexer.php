<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Search\Dto\Indexer\IndexerParameter;
use Atoolo\Search\Dto\Indexer\IndexerStatus;
use Atoolo\Search\Indexer;
use Exception;
use Solarium\QueryType\Update\Result as UpdateResult;
use Throwable;

/**
 * Implementation of the indexer on the basis of a Solr index.
 *
 * Resources are loaded via the indexer, mapped to an IndexDocument and
 * then transferred to Solr in order to index it.
 *
 * This is done in several stages:
 *
 * 1. first, the PHP files containing the resource data are determined via
 *    the file system. This can be an entire directory tree or just
 *    individual files.
 * 2. resources may have been translated into several languages and are also
 *    available in translated form as PHP files in the file system. A separate
 *    Solr index is used for each language. The PHP files are therefore
 *    assigned to the respective language.
 * 3. the resources are loaded separately for each language, mapped to the
 *    index documents and indexed for the corresponding index.
 * 4. for performance reasons, the documents are not indexed individually,
 *    but always a list of documents. The entire list is divided into chunks
 *    and indexed chunk-wise.
 */
class InternalResourceIndexer implements Indexer
{
    /**
     * @param iterable<DocumentEnricher<IndexDocument>> $documentEnricherList
     */
    public function __construct(
        private readonly iterable $documentEnricherList,
        private readonly IndexerProgressHandler $indexerProgressHandler,
        private readonly LocationFinder $finder,
        private readonly ResourceLoader $resourceLoader,
        private readonly TranslationSplitter $translationSplitter,
        private readonly SolrIndexService $indexService,
        private readonly IndexingAborter $aborter,
        private readonly string $source
    ) {
    }

    /**
     * @param string[] $idList
     */
    public function remove(string $index, array $idList): void
    {
        if (empty($idList)) {
            return;
        }

        $this->indexService->deleteByIdList($index, $this->source, $idList);
        $this->indexService->commit($index);
    }

    public function abort(string $index): void
    {
        $this->aborter->requestAbortion($index);
    }

    /**
     * Indexes an entire directory structure or only selected files
     * if `paths` was specified in `$parameter`.
     */
    public function index(IndexerParameter $parameter): IndexerStatus
    {
        if (empty($parameter->paths)) {
            $pathList = $this->finder->findAll();
        } else {
            $mappedPaths = $this->normalizePaths($parameter->paths);
            $pathList = $this->finder->findPaths($mappedPaths);
        }

        return $this->indexResources($parameter, $pathList);
    }

    /**
     * A path can signal to be translated into another language via
     * the URL parameter loc. For example,
     * `/dir/file.php?loc=it_IT` defines that the path
     * `/dir/file.php.translations/it_IT.php` is to be used.
     * This method translates the URL parameter into the correct path.
     *
     * @param string[] $pathList
     * @return string[]
     */
    private function normalizePaths(array $pathList): array
    {
        return array_map(static function ($path) {
            $queryString = parse_url($path, PHP_URL_QUERY);
            if (!is_string($queryString)) {
                return $path;
            }
            $urlPath = parse_url($path, PHP_URL_PATH);
            if (!is_string($urlPath)) {
                return $path;
            }
            parse_str($queryString, $params);
            if (!isset($params['loc']) || !is_string($params['loc'])) {
                return $urlPath;
            }
            $loc = $params['loc'];
            return $urlPath . '.translations/' . $loc . ".php";
        }, $pathList);
    }

    /**
     * Indexes the resources of all passed paths.
     *
     * @param array<string> $pathList
     */
    private function indexResources(
        IndexerParameter $parameter,
        array $pathList
    ): IndexerStatus {
        if (count($pathList) === 0) {
            return IndexerStatus::empty();
        }

        $total = count($pathList);
        if (empty($parameter->paths)) {
            $this->deleteErrorProtocol($parameter->index);
            $this->indexerProgressHandler->start($total);
        } else {
            $this->indexerProgressHandler->startUpdate($total);
        }

        $availableIndexes = $this->indexService->getAvailableIndices();
        $splitterResult = $this->translationSplitter->split($pathList);

        $this->indexTranslationSplittedResources(
            $parameter,
            $availableIndexes,
            $splitterResult
        );

        $this->indexerProgressHandler->finish();

        return $this->indexerProgressHandler->getStatus();
    }

    /**
     * There is a separate Solr index for each language. This allows
     * language-specific tokenizers and other language-relevant configurations
     * to be used. Via the `$splitterResult` all paths are separated according
     * to their languages and can be indexed separately. Each language is
     * indexed separately here.
     *
     * @param string[] $availableIndexes
     */
    private function indexTranslationSplittedResources(
        IndexerParameter $parameter,
        array $availableIndexes,
        TranslationSplitterResult $splitterResult
    ): void {

        $processId = uniqid('', true);

        if (in_array($parameter->index, $availableIndexes)) {
            $this->indexResourcesPerLanguageIndex(
                $processId,
                $parameter,
                $parameter->index,
                $splitterResult->getBases()
            );
        } else {
            $this->indexerProgressHandler->error(new Exception(
                'Index "' . $parameter->index . '" not found'
            ));
        }

        foreach ($splitterResult->getLocales() as $locale) {
            $localeIndex = $parameter->index . '-' . $locale;
            if (in_array($localeIndex, $availableIndexes)) {
                $this->indexResourcesPerLanguageIndex(
                    $processId,
                    $parameter,
                    $localeIndex,
                    $splitterResult->getTranslations($locale)
                );
            } else {
                $this->indexerProgressHandler->error(new Exception(
                    'Index "' . $localeIndex . '" not found'
                ));
            }
        }
    }

    /**
     * The resources for a language are indexed here.
     *
     * @param string[] $pathList
     */
    private function indexResourcesPerLanguageIndex(
        string $processId,
        IndexerParameter $parameter,
        string $index,
        array $pathList
    ): void {
        $offset = 0;
        $successCount = 0;

        while (true) {
            $indexedCount = $this->indexChunks(
                $processId,
                $index,
                $pathList,
                $offset,
                $parameter->chunkSize
            );
            gc_collect_cycles();
            if ($indexedCount === false) {
                break;
            }
            $successCount += $indexedCount;
            $offset += $parameter->chunkSize;
        }

        if (
            $parameter->cleanupThreshold > 0 &&
            $successCount >= $parameter->cleanupThreshold
        ) {
            $this->indexService->deleteExcludingProcessId(
                $index,
                $this->source,
                $processId
            );
        }
        $this->indexService->commit($index);
    }

    /**
     * For performance reasons, not every resource is indexed individually,
     * but the index documents are first generated from several resources.
     * These are then passed to Solr for indexing via a request. These
     * methods accept a chunk with all paths that are to be indexed via a
     * request.
     *
     * @param string[] $pathList
     */
    private function indexChunks(
        string $processId,
        string $solrCore,
        array $pathList,
        int $offset,
        int $length
    ): int|false {
        $resourceList = $this->loadResources(
            $pathList,
            $offset,
            $length
        );
        if ($resourceList === false) {
            return false;
        }
        if ($this->aborter->isAbortionRequested($solrCore)) {
            $this->aborter->resetAbortionRequest($solrCore);
            $this->indexerProgressHandler->abort();
            return false;
        }
        if (empty($resourceList)) {
            return 0;
        }
        $this->indexerProgressHandler->advance(count($resourceList));
        $result = $this->add($solrCore, $processId, $resourceList);

        if ($result->getStatus() !== 0) {
            $this->indexerProgressHandler->error(new Exception(
                $result->getResponse()->getStatusMessage()
            ));
            return 0;
        }

        return count($resourceList);
    }

    /**
     * @param string[] $pathList
     * @return Resource[]|false
     */
    private function loadResources(
        array $pathList,
        int $offset,
        int $length
    ): array|false {

        $maxLength = count($pathList) - $offset;
        if ($maxLength <= 0) {
            return false;
        }

        $end = min($length, $maxLength) + $offset;

        $resourceList = [];
        for ($i = $offset; $i < $end; $i++) {
            $path = $pathList[$i];
            try {
                $resource = $this->resourceLoader->load($path);
                $resourceList[] = $resource;
            } catch (Throwable $e) {
                $this->indexerProgressHandler->error($e);
            }
        }
        return $resourceList;
    }

    /**
     * @param array<Resource> $resources
     */
    private function add(
        string $solrCore,
        string $processId,
        array $resources
    ): UpdateResult {

        $updater = $this->indexService->updater($solrCore);

        foreach ($resources as $resource) {
            foreach ($this->documentEnricherList as $enricher) {
                if (!$enricher->isIndexable($resource)) {
                    $this->indexerProgressHandler->skip(1);
                    continue 2;
                }
            }
            try {
                /** @var IndexSchema2xDocument $doc */
                $doc = $updater->createDocument();
                foreach ($this->documentEnricherList as $enricher) {
                    /** @var IndexSchema2xDocument $doc */
                    $doc = $enricher->enrichDocument(
                        $resource,
                        $doc,
                        $processId
                    );
                }
                $updater->addDocument($doc);
            } catch (Throwable $e) {
                $this->indexerProgressHandler->error($e);
            }
        }

        // this executes the query and returns the result
        return $updater->update();
    }

    private function deleteErrorProtocol(string $core): void
    {
        $this->indexService->deleteByQuery(
            $core,
            'crawl_status:error OR crawl_status:warning'
        );
    }
}
