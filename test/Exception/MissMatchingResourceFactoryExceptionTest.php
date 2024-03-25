<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Exception;

use Atoolo\Search\Exception\MissMatchingResourceFactoryException;
use PHPUnit\Framework\TestCase;

class MissMatchingResourceFactoryExceptionTest extends TestCase
{
    public function testGetLocation(): void
    {
        $e = new MissMatchingResourceFactoryException('/test.php');
        $this->assertEquals(
            '/test.php',
            $e->getLocation(),
            'unexpected location'
        );
    }
}
