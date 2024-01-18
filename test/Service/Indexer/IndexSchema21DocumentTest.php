<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Indexer;

use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use PHPUnit\Framework\TestCase;

class IndexSchema21DocumentTest extends TestCase
{
    public function testGetFields(): void
    {
        $doc = new IndexSchema2xDocument();
        $doc->sp_id = '123';

        $this->assertEquals(
            ['sp_id' => '123'],
            $doc->getFields(),
            'unexpected fields'
        );
    }
}
