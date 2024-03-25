<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Indexer;

use DateTime;

/**
 * @phpstan-type JsonStatus array{
 *  state: ?string,
 *  statusLine: ?string,
 *  startTime: int,
 *  endTime: ?int,
 *  total: int,
 *  processed: int,
 *  skipped: ?int,
 *  lastUpdate: ?int,
 *  updated: ?int,
 *  errors: ?int
 * }
 */
class IndexerStatus
{
    public function __construct(
        public IndexerStatusState $state,
        public readonly DateTime $startTime,
        public ?DateTime $endTime,
        public int $total,
        public int $processed,
        public int $skipped,
        public DateTime $lastUpdate,
        public int $updated,
        public int $errors
    ) {
    }

    public static function empty(): IndexerStatus
    {
        $now = new DateTime();
        return new IndexerStatus(
            state: IndexerStatusState::UNKNOWN,
            startTime: $now,
            endTime: $now,
            total:0,
            processed: 0,
            skipped: 0,
            lastUpdate: $now,
            updated: 0,
            errors: 0
        );
    }

    public function getStatusLine(): string
    {
        $endTime = $this->endTime;
        if ($endTime === null || $endTime->getTimestamp() === 0) {
            $endTime = new DateTime();
        }
        $duration = $this->startTime->diff($endTime);

        $lastUpdate = $this->lastUpdate;
        if ($lastUpdate->getTimestamp() === 0) {
            $lastUpdate = $endTime;
        }
        return
            '[' . $this->state->name . '] ' .
            'start: ' . $this->startTime->format('d.m.Y H:i') . ', ' .
            'time: ' . $duration->format('%Hh %Im %Ss') . ', ' .
            'processed: ' . $this->processed . "/" . $this->total . ', ' .
            'skipped: ' . $this->skipped . ', ' .
            'lastUpdate: ' . $lastUpdate->format('d.m.Y H:i') . ', ' .
            'updated: ' . $this->updated . ', ' .
            'errors: ' . $this->errors;
    }
}
