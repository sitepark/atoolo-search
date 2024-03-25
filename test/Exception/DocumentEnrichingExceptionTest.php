<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Exception;

use Atoolo\Search\Exception\DocumentEnrichingException;
use PHPUnit\Framework\TestCase;

class DocumentEnrichingExceptionTest extends TestCase
{
    public function testGetLocation(): void
    {
        $e = new DocumentEnrichingException('/test.php');
        $this->assertEquals(
            '/test.php',
            $e->getLocation(),
            'unexpected location'
        );
    }
}
