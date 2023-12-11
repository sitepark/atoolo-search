<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Loader\SiteKitLoader;
use Atoolo\Resource\Loader\SiteKitNavigationHierarchyLoader;
use Atoolo\Resource\Loader\StaticResourceBaseLocator;
use Atoolo\Search\Console\Command\Io\IndexerProgressProgressBar;
use Atoolo\Search\Dto\Indexer\IndexerParameter;
use Atoolo\Search\Service\Indexer\SiteKit\DefaultSchema21DocumentEnricher;
use Atoolo\Search\Service\Indexer\SolrIndexer;
use Atoolo\Search\Service\SolrParameterClientFactory;
use http\Exception\InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'atoolo:indexer',
    description: 'Fill a search index'
)]
class Indexer extends Command
{
    private IndexerProgressProgressBar $progressBar;
    private SymfonyStyle $io;

    private InputInterface $input;
    private string $resourceDir;

    protected function configure(): void
    {
        $this
            ->setHelp('Command to fill a search index')
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
                'Resource directory whose data is to be indexed.'
            )
            ->addArgument(
                'directories',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Resources or directories of the resource to be indexed.'
            )
            ->addOption(
                'cleanup-threshold',
                null,
                InputArgument::OPTIONAL,
                'Specifies the number of indexed documents from ' .
                'which indexing is considered successful and old entries ' .
                'can be deleted. Is only used for full indexing.',
                0
            )
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        $this->input = $input;
        $this->io = new SymfonyStyle($input, $output);
        $this->progressBar = new IndexerProgressProgressBar($output);
        $this->resourceDir = $this->getStringArgument('resource-dir');
        $directories = (array)$input->getArgument('directories');

        $cleanupThreshold = empty($directories)
            ? $this->getIntArgument('cleanup-threshold', 0)
            : 0;

        if (empty($directories)) {
            $this->io->title('Index all resources');
        } else {
            $this->io->title('Index resources subdirectories');
            $this->io->listing($directories);
        }

        $parameter = new IndexerParameter(
            $this->getStringArgument('solr-core'),
            $cleanupThreshold,
            $directories
        );

        $indexer = $this->createIndexer();
        $indexer->index($parameter);

        $this->errorReport();

        return Command::SUCCESS;
    }

    private function getStringArgument(string $name): string
    {
        $value = $this->input->getArgument($name);
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                $name . ' must be a string'
            );
        }
        return $value;
    }

    private function getIntArgument(string $name, int $default): int
    {
        if (!$this->input->hasArgument($name)) {
            return $default;
        }
        $value = $this->input->getArgument($name);
        if (!is_int($value)) {
            throw new InvalidArgumentException(
                $name . ' must be a integer'
            );
        }
        return (int)$value;
    }

    protected function errorReport(): void
    {
        foreach ($this->progressBar->getErrors() as $error) {
            if ($error instanceof InvalidResourceException) {
                $this->io->error(
                    $error->getLocation() . ': ' .
                    $error->getMessage()
                );
            } else {
                $this->io->error($error->getMessage());
            }
        }
    }

    protected function createIndexer(): SolrIndexer
    {
        $resourceBaseLocator = new StaticResourceBaseLocator(
            $this->resourceDir
        );
        $resourceLoader = new SiteKitLoader($resourceBaseLocator);
        $navigationLoader = new SiteKitNavigationHierarchyLoader(
            $resourceLoader
        );
        $schema21 = new DefaultSchema21DocumentEnricher(
            $navigationLoader
        );

        $url = parse_url($this->getStringArgument('solr-connection-url'));

        $clientFactory = new SolrParameterClientFactory(
            $url['scheme'],
            $url['host'],
            $url['port'] ?? ($url['scheme'] === 'https' ? 443 : 8382),
            $url['path'] ?? '',
            null,
            0
        );

        return new SolrIndexer(
            [$schema21],
            $this->progressBar,
            $resourceBaseLocator,
            $resourceLoader,
            $clientFactory,
            'internal'
        );
    }
}