<?php

declare(strict_types=1);

namespace Atoolo\Search\Service;

use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * With this SolrClientFactory implementation, the necessary connection data
 * for the Solr client is transferred via the client constructor argument.
 * The SolrParameterClientFactory can be registered as a DependencyInjection
 * service by passing the necessary transfer arguments as parameters.
 */
class SolrParameterClientFactory implements SolrClientFactory
{
    public function __construct(
        private readonly string $scheme,
        private readonly string $host,
        private readonly int $port,
        private readonly string $path = '',
        private readonly ?string $proxy = null,
        private readonly int $timeout = 0
    ) {
    }

    public function create(string $core): Client
    {
        $adapter = new Curl();
        $adapter->setTimeout($this->timeout);
        $adapter->setProxy($this->proxy);
        $eventDispatcher = new EventDispatcher();
        $config = [
            'endpoint' => [
                $this->host => [
                    'scheme' => $this->scheme,
                    'host' => $this->host,
                    'port' => $this->port,
                    'path' => $this->path,
                    'core' => $core,
                ]
            ]
        ];

        // create a client instance
        return new Client(
            $adapter,
            $eventDispatcher,
            $config
        );
    }
}
