<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Indexer;

/**
 * @codeCoverageIgnore
 */
enum IndexerStatusState: string
{
    case UNKNOWN = 'UNKNOWN';
    case RUNNING = 'RUNNING';
    case FINISHED = 'FINISHED';
    case ABORTED = 'ABORTED';
}
