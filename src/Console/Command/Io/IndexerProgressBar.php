<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command\Io;

use Atoolo\Search\Dto\Indexer\IndexerStatus;
use Atoolo\Search\Dto\Indexer\IndexerStatusState;
use Atoolo\Search\Service\Indexer\IndexerProgressHandler;
use DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class IndexerProgressBar implements IndexerProgressHandler
{
    private OutputInterface $output;
    private ProgressBar $progressBar;
    private IndexerStatus $status;

    /**
     * @var array<Throwable>
     */
    private array $errors = [];

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function start(int $total): void
    {
        $this->progressBar = new ProgressBar($this->output, $total);
        $this->formatProgressBar('green');
        $this->status = new IndexerStatus(
            state: IndexerStatusState::RUNNING,
            startTime: new DateTime(),
            endTime: null,
            total: $total,
            processed: 0,
            skipped: 0,
            lastUpdate: new DateTime(),
            updated: 0,
            errors: 0
        );
    }

    public function startUpdate(int $total): void
    {
        $this->start($total);
    }

    public function advance(int $step): void
    {
        $this->progressBar->advance($step);
        $this->status->processed += $step;
    }

    public function skip(int $step): void
    {
        $this->status->skipped++;
    }

    private function formatProgressBar(string $color): void
    {
        $this->progressBar->setBarCharacter('<fg=' . $color . '>•</>');
        $this->progressBar->setEmptyBarCharacter('<fg=' . $color . '>⚬</>');
        $this->progressBar->setProgressCharacter('<fg=' . $color . '>➤</>');
        $this->progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%%\n" .
            "  %estimated:-20s%  %memory:20s%"
        );
    }

    public function error(Throwable $throwable): void
    {
        $this->formatProgressBar('red');
        $this->errors[] = $throwable;
        $this->status->errors++;
    }

    public function finish(): void
    {
        $this->progressBar->finish();
        $this->status->state = IndexerStatusState::FINISHED;
        $this->status->endTime = new DateTime();
    }

    /**
     * @return array<Throwable>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatus(): IndexerStatus
    {
        return $this->status;
    }

    public function abort(): void
    {
        $this->progressBar->finish();
        $this->status->state = IndexerStatusState::ABORTED;
        $this->status->endTime = new DateTime();
    }
}
