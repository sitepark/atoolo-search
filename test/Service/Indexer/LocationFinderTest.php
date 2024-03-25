<?php

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Resource\Loader\StaticResourceBaseLocator;
use Atoolo\Search\Service\Indexer\LocationFinder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocationFinder::class)]
class LocationFinderTest extends TestCase
{
    private LocationFinder $locationFinder;
    protected function setUp(): void
    {
        $base = realpath(
            __DIR__ . '/../../resources/Service/Indexer/LocationFinder'
        );
        if ($base === false) {
            throw new InvalidArgumentException('basepath not found');
        }
        $this->locationFinder = new LocationFinder(
            new StaticResourceBaseLocator($base)
        );
    }

    public function testFindAll(): void
    {
        $locations = $this->locationFinder->findAll();
        $this->assertEquals(
            [
                '/a.php',
                '/b/c.php',
                '/f.pdf.media.php'
            ],
            $locations,
            'unexpected locations'
        );
    }

    public function testFindPathsWithFile(): void
    {
        $locations = $this->locationFinder->findPaths(
            [
                'a.php'
            ]
        );
        $this->assertEquals(
            [
                '/a.php',
            ],
            $locations,
            'unexpected locations'
        );
    }

    public function testFindPathsWithDirectory(): void
    {
        $locations = $this->locationFinder->findPaths(
            [
                '/b',
            ]
        );
        $this->assertEquals(
            [
                '/b/c.php',
            ],
            $locations,
            'unexpected locations'
        );
    }
}
