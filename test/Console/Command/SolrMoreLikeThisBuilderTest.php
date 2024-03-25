<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Search\Console\Command\ResourceBaseLocatorBuilder;
use Atoolo\Search\Console\Command\SolrMoreLikeThisBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SolrMoreLikeThisBuilder::class)]
class SolrMoreLikeThisBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $builder = new SolrMoreLikeThisBuilder(
            $this->createStub(ResourceBaseLocatorBuilder::class)
        );
        $builder
            ->resourceDir('test.php')
            ->solrConnectionUrl('http://localhost:8382');

        $this->expectNotToPerformAssertions();
        $builder->build();
    }
}
