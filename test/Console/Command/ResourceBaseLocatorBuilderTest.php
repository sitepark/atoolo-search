<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Search\Console\Command\ResourceBaseLocatorBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceBaseLocatorBuilder::class)]
class ResourceBaseLocatorBuilderTest extends TestCase
{
    public function testBuild(): void
    {

        $resourceDir = __DIR__ .
            '/../../../var/test/ResourceBaseLocatorBuilderTest';
        $objectsDir = $resourceDir . '/objects';
        if (!is_dir($objectsDir)) {
            mkdir($objectsDir, 0777, true);
        }

        $builder = new ResourceBaseLocatorBuilder();
        $locator = $builder->build($resourceDir);

        $this->assertEquals(
            $resourceDir,
            $locator->locate(),
            'unexpected resource dir'
        );
    }
}
