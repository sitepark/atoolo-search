<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Search\Console\Command\IndexDocumentDumperBuilder;
use Atoolo\Search\Console\Command\ResourceBaseLocatorBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexDocumentDumperBuilder::class)]
class IndexDocumentDumperBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $builder = new IndexDocumentDumperBuilder(
            $this->createStub(ResourceBaseLocatorBuilder::class)
        );
        $builder->resourceDir('test');
        $builder->documentEnricherList([]);

        $this->expectNotToPerformAssertions();
        $builder->build();
    }
}
