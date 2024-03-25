<?php

namespace Atoolo\Search\Dto\Search\Query;

/**
 * @codeCoverageIgnore
 */
enum QueryOperator: string
{
    case AND = 'AND';
    case OR = 'OR';
}
