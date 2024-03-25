<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Search\Console\Application;
use Atoolo\Search\Console\Command\SolrSuggestBuilder;
use Atoolo\Search\Console\Command\Suggest;
use Atoolo\Search\Dto\Search\Result\Suggestion;
use Atoolo\Search\Dto\Search\Result\SuggestResult;
use Atoolo\Search\Service\Search\SolrSuggest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(Suggest::class)]
class SuggestTest extends TestCase
{
    private CommandTester $commandTester;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $result = new SuggestResult(
            [
                new Suggestion('security', 10),
                new Suggestion('section', 5)
            ],
            10
        );
        $solrSuggest = $this->createStub(SolrSuggest::class);
        $solrSuggest->method('suggest')
            ->willReturn($result);
        $solrSuggestBuilder = $this->createStub(
            SolrSuggestBuilder::class
        );
        $solrSuggestBuilder->method('build')
            ->willReturn($solrSuggest);

        $suggest = new Suggest(
            $solrSuggestBuilder
        );

        $application = new Application([
            $suggest
        ]);

        $command = $application->find('atoolo:suggest');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->commandTester->execute([
            'solr-connection-url' => 'http://localhost:8382',
            'solr-core' => 'test',
            'terms' => 'sec'
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertEquals(
            <<<EOF
 security (10)
 section (5)
 Query-Time: 10ms

EOF,
            $output
        );
    }
}
