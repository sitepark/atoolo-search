<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Search\Console\Command\Io\TypifiedInput;
use Atoolo\Search\Dto\Search\Query\Filter\ArchiveFilter;
use Atoolo\Search\Dto\Search\Query\Filter\ObjectTypeFilter;
use Atoolo\Search\Dto\Search\Query\SuggestQuery;
use Atoolo\Search\Dto\Search\Result\SuggestResult;
use Atoolo\Search\Service\Search\SolrSuggest;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'atoolo:suggest',
    description: 'Performs a suggest search'
)]
class Suggest extends Command
{
    private TypifiedInput $input;
    private SymfonyStyle $io;
    private string $solrCore;

    public function __construct(
        private readonly SolrSuggestBuilder $solrSuggestBuilder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Command that performs a suggest search')
            ->addArgument(
                'solr-connection-url',
                InputArgument::REQUIRED,
                'Solr connection url.'
            )
            ->addArgument(
                'solr-core',
                InputArgument::REQUIRED,
                'Solr core to be used.'
            )
            ->addArgument(
                'terms',
                InputArgument::REQUIRED,
                'Suggest terms.'
            )
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->input = new TypifiedInput($input);
        $this->io = new SymfonyStyle($input, $output);
        $this->solrCore = $this->input->getStringArgument('solr-core');
        $terms = $this->input->getStringArgument('terms');

        $search = $this->createSearcher();
        $query = $this->buildQuery($terms);

        $result = $search->suggest($query);

        $this->outputResult($result);

        return Command::SUCCESS;
    }

    protected function createSearcher(): SolrSuggest
    {
        $this->solrSuggestBuilder->solrConnectionUrl(
            $this->input->getStringArgument('solr-connection-url')
        );
        return $this->solrSuggestBuilder->build();
    }

    protected function buildQuery(string $terms): SuggestQuery
    {
        $excludeMedia = new ObjectTypeFilter(['media'], 'media');
        $excludeMedia = $excludeMedia->exclude();
        return new SuggestQuery(
            $this->solrCore,
            $terms,
            [
                new ArchiveFilter(),
                $excludeMedia
            ]
        );
    }

    protected function outputResult(SuggestResult $result): void
    {
        foreach ($result as $suggest) {
            $this->io->text(
                $suggest->term .
                ' (' . $suggest->hits . ')'
            );
        }
        $this->io->text('Query-Time: ' . $result->queryTime . 'ms');
    }
}
