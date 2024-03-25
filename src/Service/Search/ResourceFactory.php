<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Search;

use Atoolo\Resource\Resource;
use Solarium\QueryType\Select\Result\Document;

/**
 * There are different types of resources e.g.
 * - External
 * - Internal-Media
 * - Internal
 * - Customized
 *
 * This interface makes the methods available that must be implemented by the
 * resource factories. The factory can use the accept() method to check whether
 * the resource can be created with the transferred Solr document. If this is
 * the case, true must be returned and the create method called, with which the
 * resource can then be created and returned.
 */
interface ResourceFactory
{
    public function accept(Document $document): bool;
    public function create(Document $document): Resource;
}
