<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Search\Console\Command\Io\TypifiedInput;
use Atoolo\Search\Dto\Search\Query\SelectQuery;
use Atoolo\Search\Dto\Search\Query\SelectQueryBuilder;
use Atoolo\Search\Dto\Search\Result\SearchResult;
use Atoolo\Search\Service\Search\SolrSelect;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'atoolo:search',
    description: 'Performs a search'
)]
class Search extends Command
{
    private SymfonyStyle $io;
    private TypifiedInput $input;
    private string $index;

    public function __construct(
        private readonly SolrSelectBuilder $solrSelectBuilder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Command to performs a search')
            ->addArgument(
                'solr-connection-url',
                InputArgument::REQUIRED,
                'Solr connection url.'
            )
            ->addArgument(
                'index',
                InputArgument::REQUIRED,
                'Solr core to be used.'
            )
            ->addArgument(
                'resource-dir',
                InputArgument::REQUIRED,
                'Resource directory whose data is to be indexed.'
            )
            ->addArgument(
                'text',
                InputArgument::REQUIRED,
                'Text with which to search.'
            )
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        $this->input = new TypifiedInput($input);
        $this->io = new SymfonyStyle($input, $output);
        $this->index = $this->input->getStringArgument('index');

        $searcher = $this->createSearch();
        $query = $this->buildQuery($input);

        $result = $searcher->select($query);

        $this->outputResult($result);

        return Command::SUCCESS;
    }

    protected function createSearch(): SolrSelect
    {
        $this->solrSelectBuilder->resourceDir(
            $this->input->getStringArgument('resource-dir')
        );
        $this->solrSelectBuilder->solrConnectionUrl(
            $this->input->getStringArgument('solr-connection-url')
        );
        return $this->solrSelectBuilder->build();
    }

    protected function buildQuery(InputInterface $input): SelectQuery
    {
        $builder = new SelectQueryBuilder();
        $builder->index($this->index);

        $text = $this->input->getStringArgument('text');
        $builder->text($text);

        // TODO: filter

        // TODO: facet

        return $builder->build();
    }

    protected function outputResult(
        SearchResult $result
    ): void {
        $this->io->title('Results (' . $result->total . ')');
        foreach ($result as $resource) {
            $this->io->text($resource->getLocation());
        }

        if (count($result->facetGroups) > 0) {
            $this->io->title('Facets');
            foreach ($result->facetGroups as $facetGroup) {
                $this->io->section($facetGroup->key);
                $listing = [];
                foreach ($facetGroup->facets as $facet) {
                    $listing[] =
                        $facet->key .
                        ' (' . $facet->hits . ')';
                }
                $this->io->listing($listing);
            }
        }

        $this->io->text('Query-Time: ' . $result->queryTime . 'ms');
    }
}
