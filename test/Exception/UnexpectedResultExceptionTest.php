<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Exception;

use Atoolo\Search\Exception\UnexpectedResultException;
use PHPUnit\Framework\TestCase;

class UnexpectedResultExceptionTest extends TestCase
{
    public function testGetResult(): void
    {
        $e = new UnexpectedResultException('test');
        $this->assertEquals(
            'test',
            $e->getResult(),
            'unexpected result'
        );
    }
}
