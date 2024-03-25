<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Service\Indexer\IndexingAborter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexingAborter::class)]
class IndexingAborterTest extends TestCase
{
    private string $file;
    private IndexingAborter $aborter;

    public function setUp(): void
    {
        $workdir = __DIR__ .
            '/../../../var/test/IndexingAborterTest';
        if (!is_dir($workdir)) {
            mkdir($workdir, 0777, true);
        }
        $this->file = $workdir . '/background-indexer-test.abort';
        if (file_exists($this->file)) {
            unlink($this->file);
        }
        $this->aborter = new IndexingAborter($workdir);
    }

    public function testIsAbortionRequested(): void
    {
        $this->assertFalse(
            $this->aborter->isAbortionRequested('test'),
            'should not aborted'
        );
    }
    public function testIsAbortionRequestedWithExistsMarkerFile(): void
    {
        touch($this->file);
        $this->assertTrue(
            $this->aborter->isAbortionRequested('test'),
            'should not aborted'
        );
    }

    public function testRequestAbortion(): void
    {
        $this->aborter->requestAbortion('test');
        $this->assertFileExists(
            $this->file,
            'requestAbortion call should create file'
        );
    }

    public function testResetAbortionRequest(): void
    {
        touch($this->file);
        $this->aborter->resetAbortionRequest('test');
        $this->assertFileDoesNotExist(
            $this->file,
            'resetAbortionRequest call should remove file'
        );
    }
}
