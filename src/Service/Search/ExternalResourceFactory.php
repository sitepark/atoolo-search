<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Search;

use Atoolo\Resource\Resource;
use LogicException;
use Solarium\QueryType\Select\Result\Document;

/**
 * External resources are data that is not provided by the CMS, but are
 * transferred to the Solr index via special indexers, for example.
 * In these cases, the search result leads to an external page.
 * This factory recognizes external content using the Solr document field url.
 * It is an external resource if the URL begins with https:// or http://.
 */
class ExternalResourceFactory implements ResourceFactory
{
    public function accept(Document $document): bool
    {
        $location = $this->getField($document, 'url');
        if ($location === null) {
            return false;
        }
        return (
            str_starts_with($location, 'http://') ||
            str_starts_with($location, 'https://')
        );
    }

    public function create(Document $document): Resource
    {
        $location = $this->getField($document, 'url');
        if ($location === null) {
            throw new LogicException('document should contain an url');
        }

        return new Resource(
            $location,
            $this->getField($document, 'sp_id') ?? '',
            $this->getField($document, 'title') ?? '',
            'external',
            []
        );
    }

    private function getField(Document $document, string $name): ?string
    {
        return $document->getFields()[$name] ?? null;
    }
}
