<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service;

use Atoolo\Search\Service\SolrParameterClientFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SolrParameterClientFactory::class)]
class SolrParameterClientFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new SolrParameterClientFactory(
            'http',
            'localhost',
            8080
        );
        $client = $factory->create('myindex');
        $this->assertNotNull($client, 'client instance expected');
    }
}
