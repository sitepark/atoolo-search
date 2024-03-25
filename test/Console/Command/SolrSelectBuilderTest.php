<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Search\Console\Command\ResourceBaseLocatorBuilder;
use Atoolo\Search\Console\Command\SolrSelectBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SolrSelectBuilder::class)]
class SolrSelectBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $builder = new SolrSelectBuilder(
            $this->createStub(ResourceBaseLocatorBuilder::class)
        );
        $builder->resourceDir('test')
            ->solrConnectionUrl('http://localhost:8382');

        $this->expectNotToPerformAssertions();
        $builder->build();
    }
}
