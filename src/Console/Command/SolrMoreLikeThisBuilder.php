<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Resource\Loader\SiteKitLoader;
use Atoolo\Search\Service\Search\ExternalResourceFactory;
use Atoolo\Search\Service\Search\InternalMediaResourceFactory;
use Atoolo\Search\Service\Search\InternalResourceFactory;
use Atoolo\Search\Service\Search\SolrMoreLikeThis;
use Atoolo\Search\Service\Search\SolrResultToResourceResolver;
use Atoolo\Search\Service\SolrParameterClientFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class SolrMoreLikeThisBuilder
{
    private string $resourceDir;
    private string $solrConnectionUrl;

    public function __construct(
        private readonly ResourceBaseLocatorBuilder $resourceBaseLocatorBuilder,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function resourceDir(string $resourceDir): SolrMoreLikeThisBuilder
    {
        $this->resourceDir = $resourceDir;
        return $this;
    }

    public function solrConnectionUrl(
        string $solrConnectionUrl
    ): SolrMoreLikeThisBuilder {
        $this->solrConnectionUrl = $solrConnectionUrl;
        return $this;
    }

    public function build(): SolrMoreLikeThis
    {
        $resourceBaseLocator = $this->resourceBaseLocatorBuilder->build(
            $this->resourceDir
        );
        $resourceLoader = new SiteKitLoader($resourceBaseLocator);
        /** @var string[] */
        $url = parse_url($this->solrConnectionUrl);
        $clientFactory = new SolrParameterClientFactory(
            $url['scheme'],
            $url['host'],
            (int)($url['port'] ?? ($url['scheme'] === 'https' ? 443 : 8983)),
            $url['path'] ?? '',
            null,
            0
        );
        $resourceFactoryList = [
            new ExternalResourceFactory(),
            new InternalResourceFactory($resourceLoader),
            new InternalMediaResourceFactory($resourceLoader)
        ];
        $solrResultToResourceResolver = new SolrResultToResourceResolver(
            $resourceFactoryList,
            $this->logger
        );

        return new SolrMoreLikeThis(
            $clientFactory,
            $solrResultToResourceResolver
        );
    }
}
