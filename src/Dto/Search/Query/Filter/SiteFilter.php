<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

/**
 * @codeCoverageIgnore
 */
class SiteFilter extends FieldFilter
{
    /**
     * @param string[] $site
     */
    public function __construct(
        array $site,
        ?string $key = null,
    ) {
        parent::__construct(
            'sp_site',
            $site,
            $key
        );
    }
}
