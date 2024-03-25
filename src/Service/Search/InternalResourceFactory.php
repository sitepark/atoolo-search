<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Search;

use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use LogicException;
use Solarium\QueryType\Select\Result\Document;

/**
 * Internal resources are the data that the CMS provides in the form of
 * aggregated PHP files. The Solr document field url specifies which resource
 * is required. If the URL ends with .php, this factory is used.
 */
class InternalResourceFactory implements ResourceFactory
{
    public function __construct(
        private readonly ResourceLoader $resourceLoader
    ) {
    }

    public function accept(Document $document): bool
    {
        $location = $this->getField($document, 'url');
        if ($location === null) {
            return false;
        }
        return str_ends_with($location, '.php');
    }

    public function create(Document $document): Resource
    {
        $location = $this->getField($document, 'url');
        if ($location === null) {
            throw new LogicException('document should contain an url');
        }
        return $this->resourceLoader->load($location);
    }

    private function getField(Document $document, string $name): ?string
    {
        return $document->getFields()[$name] ?? null;
    }
}
