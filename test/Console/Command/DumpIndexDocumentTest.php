<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Search\Console\Application;
use Atoolo\Search\Console\Command\DumpIndexDocument;
use Atoolo\Search\Console\Command\IndexDocumentDumperBuilder;
use Atoolo\Search\Service\Indexer\IndexDocumentDumper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DumpIndexDocument::class)]
class DumpIndexDocumentTest extends TestCase
{
    private CommandTester $commandTester;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $dumper = $this->createStub(IndexDocumentDumper::class);
        $dumper->method('dump')
            ->willReturn([
                ['sp_id' => '123']
            ]);
        $dumperBuilder = $this->createStub(IndexDocumentDumperBuilder::class);
        $dumperBuilder->method('resourceDir')
            ->willReturn($dumperBuilder);
        $dumperBuilder->method('documentEnricherList')
            ->willReturn($dumperBuilder);
        $dumperBuilder->method('build')
            ->willReturn($dumper);

        $dumperCommand = new DumpIndexDocument(
            [],
            $dumperBuilder
        );

        $application = new Application([
            $dumperCommand
        ]);

        $command = $application->find('atoolo:dump-index-document');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->commandTester->execute([
            'resource-dir' => 'abc',
            'paths' => ['test.php']
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        $this->assertEquals(
            <<<EOF
{
    "sp_id": "123"
}

EOF,
            $output
        );
    }
}
