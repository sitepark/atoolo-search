<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Search\Console\Command\SolrSuggestBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SolrSuggestBuilder::class)]
class SolrSuggestBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $builder = new SolrSuggestBuilder();
        $builder->solrConnectionUrl('http://localhost:8283');

        $this->expectNotToPerformAssertions();
        $builder->build();
    }
}
