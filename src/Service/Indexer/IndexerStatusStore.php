<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Search\Dto\Indexer\IndexerStatus;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class IndexerStatusStore
{
    private readonly Serializer $serializer;

    public function __construct(private readonly string $basedir)
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new BackedEnumNormalizer(),
            new DateTimeNormalizer(),
            new PropertyNormalizer()
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @throws ExceptionInterface
     */
    public function load(string $index): IndexerStatus
    {
        $file = $this->getStatusFile($index);

        if (!file_exists($file)) {
            return IndexerStatus::empty();
        }

        if (!is_readable($file)) {
            throw new InvalidArgumentException('Cannot read file ' . $file);
        }

        $json = file_get_contents($file);
        if ($json === false) {
            // @codeCoverageIgnoreStart
            $message = 'Failed to read file ' . $file;
            $error = error_get_last();
            if ($error !== null) {
                $message .= ': ' . $error['message'];
            }
            throw new RuntimeException($message);
            // @codeCoverageIgnoreEnd
        }

        /** @var IndexerStatus $status */
        $status = $this
            ->serializer
            ->deserialize($json, IndexerStatus::class, 'json');

        return $status;
    }

    public function store(string $index, IndexerStatus $status): void
    {
        $this->createBaseDirectory();

        $file = $this->getStatusFile($index);
        if (file_exists($file) && !is_writable($file)) {
            throw new RuntimeException(
                'File ' . $file . ' is not writable'
            );
        }
        $json = $this
            ->serializer
            ->serialize($status, 'json');
        $result = file_put_contents($file, $json);
        if ($result === false) {
            // @codeCoverageIgnoreStart
            $message = 'Unable to write indexer-status file ' . $file;
            $error = error_get_last();
            if ($error !== null) {
                $message .= ': ' . $error['message'];
            }
            throw new RuntimeException($message);
            // @codeCoverageIgnoreEnd
        }
    }

    private function createBaseDirectory(): void
    {
        if (
            !is_dir($concurrentDirectory = $this->basedir) &&
            (
                !@mkdir(
                    $concurrentDirectory,
                    0777,
                    true
                ) &&
                !is_dir($concurrentDirectory)
            )
        ) {
            throw new RuntimeException(sprintf(
                'Directory "%s" was not created',
                $concurrentDirectory
            ));
        }

        if (!is_writable($this->basedir)) {
            throw new RuntimeException(
                'Directory ' . $this->basedir . ' is not writable'
            );
        }
    }

    private function getStatusFile(string $index): string
    {
        return $this->basedir .
            '/atoolo.search.index.' . $index . ".status.json";
    }
}
