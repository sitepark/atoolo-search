<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Search\Console\Command\Io\TypifiedInput;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use JsonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'atoolo:dump-index-document',
    description: 'Dump a index document'
)]
class DumpIndexDocument extends Command
{
    /**
     * phpcs:ignore
     * @param iterable<DocumentEnricher<IndexDocument>> $documentEnricherList
     */
    public function __construct(
        private readonly iterable $documentEnricherList,
        private readonly IndexDocumentDumperBuilder $indexDocumentDumperBuilder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Command to dump a index-document')
            ->addArgument(
                'resource-dir',
                InputArgument::REQUIRED,
                'Resource directory whose data is to be indexed.'
            )
            ->addArgument(
                'paths',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Resources paths or directories of resources to be indexed.'
            )
        ;
    }

    /**
     * @throws JsonException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        $typedInput = new TypifiedInput($input);

        $resourceDir = $typedInput->getStringArgument('resource-dir');

        $dumper = $this->indexDocumentDumperBuilder
            ->resourceDir($resourceDir)
            ->documentEnricherList($this->documentEnricherList)
            ->build();

        $paths = $typedInput->getArrayArgument('paths');
        $dump = $dumper->dump($paths);

        foreach ($dump as $fields) {
            $output->writeln(json_encode(
                $fields,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
            ));
        }

        return Command::SUCCESS;
    }
}
