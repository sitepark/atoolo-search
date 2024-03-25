<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Resource\Loader\SiteKitLoader;
use Atoolo\Search\Service\Search\ExternalResourceFactory;
use Atoolo\Search\Service\Search\InternalMediaResourceFactory;
use Atoolo\Search\Service\Search\InternalResourceFactory;
use Atoolo\Search\Service\Search\SiteKit\DefaultBoostModifier;
use Atoolo\Search\Service\Search\SolrResultToResourceResolver;
use Atoolo\Search\Service\Search\SolrSelect;
use Atoolo\Search\Service\SolrParameterClientFactory;

class SolrSelectBuilder
{
    private string $resourceDir;
    private string $solrConnectionUrl;

    public function __construct(
        private readonly ResourceBaseLocatorBuilder $resourceBaseLocatorBuilder
    ) {
    }

    public function resourceDir(string $resourceDir): SolrSelectBuilder
    {
        $this->resourceDir = $resourceDir;
        return $this;
    }

    public function solrConnectionUrl(
        string $solrConnectionUrl
    ): SolrSelectBuilder {
        $this->solrConnectionUrl = $solrConnectionUrl;
        return $this;
    }

    public function build(): SolrSelect
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
        $defaultBoosting = new DefaultBoostModifier();

        $resourceFactoryList = [
            new ExternalResourceFactory(),
            new InternalResourceFactory($resourceLoader),
            new InternalMediaResourceFactory($resourceLoader)
        ];

        $solrResultToResourceResolver = new SolrResultToResourceResolver(
            $resourceFactoryList
        );

        return new SolrSelect(
            $clientFactory,
            $solrResultToResourceResolver,
            [$defaultBoosting],
        );
    }
}
