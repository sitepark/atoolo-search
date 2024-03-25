<?php

declare(strict_types=1);

namespace Atoolo\Search\Service;

use Solarium\Client;

/**
 * This interface is provided to make it possible to create a Solr client in
 * various ways.
 */
interface SolrClientFactory
{
    public function create(string $core): Client;
}
