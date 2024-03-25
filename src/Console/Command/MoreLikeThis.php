<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Search\Console\Command\Io\TypifiedInput;
use Atoolo\Search\Dto\Search\Query\MoreLikeThisQuery;
use Atoolo\Search\Dto\Search\Result\SearchResult;
use Atoolo\Search\Service\Search\SolrMoreLikeThis;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'atoolo:mlt',
    description: 'Performs a more-like-this search'
)]
class MoreLikeThis extends Command
{
    private SymfonyStyle $io;
    private TypifiedInput $input;
    private string $solrCore;

    public function __construct(
        private readonly SolrMoreLikeThisBuilder $solrMoreLikeThisBuilder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Command to performs a more-like-this search')
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
                'resource-dir',
                InputArgument::REQUIRED,
                'Resource directory where the resources can be found.'
            )
            ->addArgument(
                'location',
                InputArgument::REQUIRED,
                'Location of the resource to which the MoreLikeThis ' .
                'search is to be applied.'
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
        $location = $this->input->getStringArgument('location');

        $searcher = $this->createSearcher();
        $query = $this->buildQuery($location);
        $result = $searcher->moreLikeThis($query);
        $this->outputResult($result);

        return Command::SUCCESS;
    }

    protected function createSearcher(): SolrMoreLikeThis
    {
        $this->solrMoreLikeThisBuilder->solrConnectionUrl(
            $this->input->getStringArgument('solr-connection-url')
        );
        $this->solrMoreLikeThisBuilder->resourceDir(
            $this->input->getStringArgument('resource-dir')
        );

        return $this->solrMoreLikeThisBuilder->build();
    }

    protected function buildQuery(string $location): MoreLikeThisQuery
    {
        $filterList = [];
        return new MoreLikeThisQuery(
            $this->solrCore,
            $location,
            $filterList,
            5,
            ['content']
        );
    }

    protected function outputResult(SearchResult $result): void
    {
        $this->io->text($result->total . " Results:");
        foreach ($result as $resource) {
            $this->io->text($resource->getLocation());
        }
        $this->io->text('Query-Time: ' . $result->queryTime . 'ms');
    }
}
