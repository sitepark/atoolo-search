<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Resource\Resource;
use Atoolo\Search\Exception\DocumentEnrichingException;

/**
 * This interface can be used to implement enricher with the help of which a
 * Solr document can be enriched on the basis of a resource.
 *
 * @template T of IndexDocument
 */
interface DocumentEnricher
{
    /**
     * @template E of T
     * @param E $doc
     * @return E
     * @throws DocumentEnrichingException
     */
    public function enrichDocument(
        Resource $resource,
        IndexDocument $doc,
        string $processId,
    ): IndexDocument;

    /**
     * Can be used, for example, to clear the loader's
     * cache if the loader uses a cache.
     */
    public function cleanup(): void;
}
