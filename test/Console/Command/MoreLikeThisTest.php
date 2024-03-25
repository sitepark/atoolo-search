<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command;

use Atoolo\Resource\Resource;
use Atoolo\Search\Console\Application;
use Atoolo\Search\Console\Command\MoreLikeThis;
use Atoolo\Search\Console\Command\SolrMoreLikeThisBuilder;
use Atoolo\Search\Dto\Search\Result\SearchResult;
use Atoolo\Search\Service\Search\SolrMoreLikeThis;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(MoreLikeThis::class)]
class MoreLikeThisTest extends TestCase
{
    private CommandTester $commandTester;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $resultResource = $this->createStub(Resource::class);
        $resultResource->method('getLocation')
            ->willReturn('/test2.php');
        $result = new SearchResult(
            1,
            1,
            0,
            [$resultResource],
            [],
            10
        );
        $solrMoreLikeThis = $this->createStub(SolrMoreLikeThis::class);
        $solrMoreLikeThis->method('moreLikeThis')
            ->willReturn($result);
        $moreLikeThisBuilder = $this->createStub(
            SolrMoreLikeThisBuilder::class
        );
        $moreLikeThisBuilder->method('build')
            ->willReturn($solrMoreLikeThis);

        $moreLinkeThis = new MoreLikeThis(
            $moreLikeThisBuilder
        );

        $application = new Application([
            $moreLinkeThis
        ]);

        $command = $application->find('atoolo:mlt');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->commandTester->execute([
            // pass arguments to the helper
            'resource-dir' => 'abc',
            'solr-connection-url' => 'http://localhost:8080',
            'solr-core' => 'test',
            'location' => '/test.php'
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertEquals(
            <<<EOF
 1 Results:
 /test2.php
 Query-Time: 10ms

EOF,
            $output
        );
    }
}
