<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command\Io;

use Atoolo\Search\Console\Command\Io\IndexerProgressBarFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(IndexerProgressBarFactory::class)]
class IndexerProgressBarFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new IndexerProgressBarFactory();
        $this->expectNotToPerformAssertions();
        $factory->create($this->createStub(OutputInterface::class));
    }
}
