<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command\Io;

use Symfony\Component\Console\Output\OutputInterface;

class IndexerProgressBarFactory
{
    public function create(OutputInterface $output): IndexerProgressBar
    {
        $progressBar =  new IndexerProgressBar();
        $progressBar->init($output);
        return $progressBar;
    }
}
