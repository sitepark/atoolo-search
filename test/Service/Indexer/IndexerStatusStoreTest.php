<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Dto\Indexer\IndexerStatus;
use Atoolo\Search\Dto\Indexer\IndexerStatusState;
use Atoolo\Search\Service\Indexer\IndexerStatusStore;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[CoversClass(IndexerStatusStore::class)]
class IndexerStatusStoreTest extends TestCase
{
    private const TEST_DIR = __DIR__ . '/../../../var/test/IndexerStatusStore';

    public function setUp(): void
    {
        $filesystem = new Filesystem();
        // clear TEST_DIR
        if ($filesystem->exists(self::TEST_DIR)) {
            $filesystem->chmod(self::TEST_DIR, 0777, 0000, true);
            $filesystem->remove(self::TEST_DIR);
        }
        $filesystem->mkdir(self::TEST_DIR);
    }

    public function testStore(): void
    {
        $status = $this->createIndexerStatus();

        $store = new IndexerStatusStore(self::TEST_DIR);
        $store->store('test', $status);

        $json = file_get_contents(
            self::TEST_DIR . '/atoolo.search.index.test.status.json'
        );

        $expected =
            '{' .
            '"state":"FINISHED",' .
            '"startTime":"2024-01-31T11:15:10+00:00",' .
            '"endTime":"2024-01-31T12:16:11+00:00",' .
            '"total":10,' .
            '"processed":5,' .
            '"skipped":4,' .
            '"lastUpdate":"2024-01-31T13:17:12+00:00",' .
            '"updated":6,' .
            '"errors":2' .
            '}';

        $this->assertEquals($expected, $json, 'unexpected json string');
    }

    public function testStoreWithNonExistsBaseDir(): void
    {
        $status = $this->createIndexerStatus();
        $baseDir = self::TEST_DIR . '/not-exists';
        $store = new IndexerStatusStore($baseDir);

        $store->store('test', $status);

        $this->assertDirectoryExists(
            $baseDir,
            'non exists basedir should be created'
        );
    }

    public function testStoreWithNonWritableBaseDir(): void
    {
        $status = $this->createIndexerStatus();
        $baseDir = self::TEST_DIR . '/non-writable';

        $filesystem = new Filesystem();
        $filesystem->mkdir($baseDir);
        $filesystem->chmod($baseDir, 0000);

        $store = new IndexerStatusStore($baseDir);

        $this->expectException(RuntimeException::class);
        $store->store('test', $status);
    }

    public function testStoreWithNonWritableStatusFile(): void
    {
        $status = $this->createIndexerStatus();
        $baseDir = self::TEST_DIR . '/writable';

        $filesystem = new Filesystem();
        $filesystem->mkdir($baseDir);

        $file = $baseDir . '/atoolo.search.index.test-not-writable.status.json';
        touch($file);
        $filesystem->chmod($file, 0000);

        $store = new IndexerStatusStore($baseDir);

        $this->expectException(RuntimeException::class);
        $store->store('test-not-writable', $status);
    }

    public function testStoreWithBaseDirNoADirectory(): void
    {
        $status = $this->createIndexerStatus();
        $baseDir = self::TEST_DIR . '/non-dir';
        touch($baseDir);

        $store = new IndexerStatusStore($baseDir);

        $this->expectException(RuntimeException::class);
        $store->store('test', $status);
    }

    public function testStoreWithNonCreatableBaseDir(): void
    {
        $status = $this->createIndexerStatus();
        $nonWritable = self::TEST_DIR . '/non-writable';

        $filesystem = new Filesystem();
        $filesystem->mkdir($nonWritable);
        $filesystem->chmod($nonWritable, 0000);

        $store = new IndexerStatusStore($nonWritable . '/subdir');

        $this->expectException(RuntimeException::class);
        $store->store('test', $status);
    }

    public function testLoad(): void
    {
        $baseDir = __DIR__ . '/../../resources/' .
            'Service/Indexer/IndexerStatusStore';

        $store = new IndexerStatusStore($baseDir);

        $status = $store->load('test');

        $expected = $this->createIndexerStatus();
        $this->assertEquals(
            $expected,
            $status,
            'unexpected status'
        );
    }

    public function testLoadFileNotExists(): void
    {
        $baseDir = __DIR__ . '/../../resources/' .
            'Service/Indexer/IndexerStatusStore';

        $store = new IndexerStatusStore($baseDir);

        $status = $store->load('test-not-exists');

        $this->assertEquals(
            0,
            $status->total,
            'empty status expected'
        );
    }

    /**
     * @throws ExceptionInterface
     */
    public function testLoadFileNotReadable(): void
    {
        $file = self::TEST_DIR . '/' .
            'atoolo.search.index.test-not-readable.status.json';

        $filesystem = new Filesystem();
        $filesystem->touch($file);
        $filesystem->chmod($file, 0000);

        $store = new IndexerStatusStore(self::TEST_DIR);

        $this->expectException(InvalidArgumentException::class);
        $store->load('test-not-readable');
    }

    private function createIndexerStatus(): IndexerStatus
    {

        $startTime = new \DateTime();
        $startTime->setDate(2024, 1, 31);
        $startTime->setTime(11, 15, 10);

        $endTime = new \DateTime();
        $endTime->setDate(2024, 1, 31);
        $endTime->setTime(12, 16, 11);

        $lastUpdate = new \DateTime();
        $lastUpdate->setDate(2024, 1, 31);
        $lastUpdate->setTime(13, 17, 12);

        return new IndexerStatus(
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
}
