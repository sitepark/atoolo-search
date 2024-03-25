<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Console\Command\Io;

use Atoolo\Search\Console\Command\Io\IndexerProgressBar;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @phpcs:disable Generic.Files.LineLength
 */
#[CoversClass(IndexerProgressBar::class)]
class IndexerProgressBarTest extends TestCase
{
    public function testStart(): void
    {
        $output = $this->createStub(OutputInterface::class);
        $progressBar = new IndexerProgressBar($output);
        $progressBar->start(10);

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*processed: 0\/10,/',
            $progressBar->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testStartUpdate(): void
    {
        $output = $this->createStub(OutputInterface::class);
        $progressBar = new IndexerProgressBar($output);

        $progressBar->startUpdate(10);

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*processed: 0\/10,/',
            $progressBar->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    /**
     * @phpcs:disable Generic.Files.LineLength
     */
    public function testAdvance(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $progressBar = new IndexerProgressBar($output);
        $progressBar->start(10);

        $output->expects($this->once())
            ->method('write')
            ->with($this->stringStartsWith(<<<END

 1/10 [<fg=green>•</><fg=green>•</><fg=green>➤</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</><fg=green>⚬</>]  10%
  < 1 sec
END
            ));

        $progressBar->advance(1);
    }

    public function testSkip(): void
    {
        $output = $this->createStub(OutputInterface::class);
        $progressBar = new IndexerProgressBar($output);

        $progressBar->start(10);

        $progressBar->skip(10);

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*skipped: 1,/',
            $progressBar->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testError(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $progressBar = new IndexerProgressBar($output);
        $progressBar->start(10);


        //phpcs:ignore Generic.Files.LineLength.TooLong
        $output->expects($this->once())
            ->method('write')
            ->with($this->stringStartsWith(<<<END
 0/10 [<fg=red>➤</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</><fg=red>⚬</>]   0%
  < 1 sec
END
            ));

        $progressBar->error(new Exception('test'));
        $progressBar->advance(0);
    }

    public function testGetError(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $progressBar = new IndexerProgressBar($output);
        $progressBar->start(10);

        $progressBar->error(new Exception('test'));

        $this->assertCount(
            1,
            $progressBar->getErrors(),
            'unexpected error count'
        );
    }

    public function testFinish(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $progressBar = new IndexerProgressBar($output);
        $progressBar->start(10);


        $output->expects($this->once())
            ->method('write')
            ->with($this->stringStartsWith(<<<END

10/10 [<fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</>] 100%
  < 1 sec
END
            ));

        $progressBar->finish();
    }

    public function testAbort(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $progressBar = new IndexerProgressBar($output);
        $progressBar->start(10);


        $output->expects($this->once())
            ->method('write')
            ->with($this->stringStartsWith(<<<END

10/10 [<fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</><fg=green>•</>] 100%
  < 1 sec
END
            ));

        $progressBar->abort();
    }
}
