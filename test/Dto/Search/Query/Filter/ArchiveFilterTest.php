<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Dto\Search\Query\Filter;

use Atoolo\Search\Dto\Search\Query\Filter\ArchiveFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArchiveFilter::class)]
class ArchiveFilterTest extends TestCase
{
    public function testAndQuery(): void
    {
        $filter = new ArchiveFilter();
        $this->assertEquals(
            '-sp_archive:true',
            $filter->getQuery(),
            'unexpected query'
        );
    }
}
