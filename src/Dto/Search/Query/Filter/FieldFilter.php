<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query\Filter;

use InvalidArgumentException;

class FieldFilter extends Filter
{
    /**
     * @var string[]
     */
    private readonly array $values;

    public function __construct(
        ?string $key,
        private readonly string $field,
        string ...$values
    ) {
        if (count($values) === 0) {
            throw new InvalidArgumentException(
                'values is an empty array'
            );
        }
        $this->values = $values;
        parent::__construct(
            $key,
            $key !== null ? [$key] : []
        );
    }

    public function getQuery(): string
    {
        $filterValue = count($this->values) === 1
            ? $this->values[0]
            : '('  . implode(' ', $this->values) . ')';
        return $this->field . ':' . $filterValue;
    }

    public function exclude(): FieldFilter
    {
        $field = $this->field;
        if (!str_starts_with($field, '-')) {
            $field = '-' . $field;
        }
        return new FieldFilter(
            $this->key,
            $field,
            ...$this->values
        );
    }
}
