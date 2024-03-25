<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Dto\Indexer\IndexerStatus;
use Atoolo\Search\Service\Indexer\BackgroundIndexerProgressState;
use Atoolo\Search\Service\Indexer\IndexerStatusStore;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(BackgroundIndexerProgressState::class)]
class BackgroundIndexerProgressStateTest extends TestCase
{
    private IndexerStatusStore&MockObject $statusStore;
    private BackgroundIndexerProgressState $state;

    public function setUp(): void
    {
        $this->statusStore = $this->createMock(IndexerStatusStore::class);
        $this->state = new BackgroundIndexerProgressState(
            'test',
            $this->statusStore
        );
    }

    public function testStart(): void
    {
        $this->state->start(10);

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*processed: 0\/10,/',
            $this->state->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testUpdate(): void
    {

        $this->statusStore
            ->method('load')
            ->willReturn(IndexerStatus::empty());

        $this->state->startUpdate(10);

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*processed: 0\/10,/',
            $this->state->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testAdvance(): void
    {

        $this->state->start(10);

        $this->statusStore->expects($this->once())
            ->method('store');

        $this->state->advance(1);

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*processed: 1\/10,/',
            $this->state->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testAdvanceForUpdate(): void
    {

        $this->statusStore
            ->method('load')
            ->willReturn(IndexerStatus::empty());

        $this->state->startUpdate(10);

        $this->statusStore->expects($this->once())
            ->method('store');

        $this->state->advance(1);

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*processed: 1\/10,.*updated: 1,/',
            $this->state->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testSkip(): void
    {

        $this->state->start(10);

        $this->state->skip(1);

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*skipped: 1,/',
            $this->state->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testError(): void
    {
        $this->state->start(10);

        $this->state->error(new Exception('test'));

        $this->assertMatchesRegularExpression(
            '/\[RUNNING].*errors: 1$/',
            $this->state->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testFinish(): void
    {
        $this->state->start(10);

        $this->statusStore->expects($this->once())
            ->method('store');

        $this->state->finish();

        $this->assertMatchesRegularExpression(
            '/\[FINISHED]/',
            $this->state->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }

    public function testAbort(): void
    {
        $this->state->start(10);

        $this->state->abort();

        $this->assertMatchesRegularExpression(
            '/\[ABORTED]/',
            $this->state->getStatus()->getStatusLine(),
            "unexpected status line"
        );
    }
}
