<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Resource\ResourceLoader;

class IndexDocumentDumper
{
    /**
     * @param iterable<DocumentEnricher<IndexDocument>> $documentEnricherList
     */
    public function __construct(
        private readonly ResourceLoader $resourceLoader,
        private readonly iterable $documentEnricherList
    ) {
    }

    /**
     * @param string[] $paths
     * @return array<int,array<string,mixed>>
     *    Returns the raw array data of the documents to be able to
     *    output them as JSON, for example.
     */
    public function dump(array $paths): array
    {
        $documents = [];
        foreach ($paths as $path) {
            $resource = $this->resourceLoader->load($path);
            $doc = new IndexSchema2xDocument();
            $processId = 'process-id';

            foreach ($this->documentEnricherList as $enricher) {
                /** @var IndexSchema2xDocument $doc */
                $doc = $enricher->enrichDocument(
                    $resource,
                    $doc,
                    $processId
                );
            }

            $documents[] = $doc->getFields();
        }

        return $documents;
    }
}
