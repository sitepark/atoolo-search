<?php

declare(strict_types=1);

namespace Atoolo\Search\Test\Service\Search\SiteKit;

use Atoolo\Search\Service\Search\SiteKit\DefaultBoostModifier;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Select\Query\Query as SelectQuery;

class DefaultBoostModifierTest extends TestCase
{
    public function testModify(): void
    {
        $modifier = new DefaultBoostModifier();

        $query = new SelectQuery();
        $modifiedQuery = $modifier->modify($query);

        $this->assertNotNull($modifiedQuery->getDisMax()->getBoostQueries());
    }
}
