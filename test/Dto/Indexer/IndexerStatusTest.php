<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Dto\Indexer;

use Atoolo\Search\Dto\Indexer\IndexerStatus;
use Atoolo\Search\Dto\Indexer\IndexerStatusState;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexerStatus::class)]
class IndexerStatusTest extends TestCase
{
    private IndexerStatus $status;

    public function setUp(): void
    {
        $startTime = new DateTime();
        $startTime->setDate(2024, 1, 31);
        $startTime->setTime(11, 15, 10);

        $endTime = new DateTime();
        $endTime->setDate(2024, 1, 31);
        $endTime->setTime(12, 16, 11);

        $lastUpdate = new DateTime();
        $lastUpdate->setDate(2024, 1, 31);
        $lastUpdate->setTime(13, 17, 12);

        $this->status = new IndexerStatus(
            IndexerStatusState::FINISHED,
            $startTime,
            $endTime,
            10,
            5,
            4,
            $lastUpdate,
            6,
            2
        );
    }

    public function testGetStatus(): void
    {
        $this->assertEquals(
            '[FINISHED] ' .
                'start: 31.01.2024 11:15, ' .
                'time: 01h 01m 01s, ' .
                'processed: 5/10, ' .
                'skipped: 4, ' .
                'lastUpdate: 31.01.2024 13:17, ' .
                'updated: 6, ' .
                'errors: 2',
            $this->status->getStatusLine(),
            "unexpected status line"
        );
    }
    public function testEmpty(): void
    {
        $status = IndexerStatus::empty();

        $dateTimePattern = '[0-9]{2}\.[0-9]{2}\.[0-9]{4} [0-9]{2}:[0-9]{2}';
        $patter = '/\[UNKNOWN] ' .
            'start: ' . $dateTimePattern . ', ' .
            'time: 00h 00m 00s, ' .
            'processed: 0\/0, ' .
            'skipped: 0, ' .
            'lastUpdate: ' . $dateTimePattern . ', ' .
            'updated: 0, ' .
            'errors: 0' .
            '/';

        $this->assertMatchesRegularExpression(
            $patter,
            $status->getStatusLine(),
            "unexpected status line for empty status"
        );
    }

    public function testStatusLineWithoutEndTime(): void
    {
        $startTime = new DateTime();
        $startTime->setDate(2024, 1, 31);
        $startTime->setTime(11, 15, 10);

        $lastUpdate = new DateTime();
        $lastUpdate->setTimestamp(0);

        $status = new IndexerStatus(
            IndexerStatusState::UNKNOWN,
            $startTime,
            null,
            0,
            0,
            0,
            $lastUpdate,
            0,
            0
        );

        $dateTimePattern = '[0-9]{2}\.[0-9]{2}\.[0-9]{4} [0-9]{2}:[0-9]{2}';
        $patter = '/lastUpdate: ' . $dateTimePattern . ', /';

        $this->assertMatchesRegularExpression(
            $patter,
            $status->getStatusLine(),
            "unexpected status line without endTime"
        );
    }
}
