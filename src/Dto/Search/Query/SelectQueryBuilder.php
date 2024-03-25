<?php

declare(strict_types=1);

namespace Atoolo\Search\Dto\Search\Query;

use Atoolo\Search\Dto\Search\Query\Facet\Facet;
use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\Sort\Criteria;

class SelectQueryBuilder
{
    private string $index = '';
    private string $text = '';
    private int $offset = 0;
    private int $limit = 10;
    /**
     * @var Criteria[]
     */
    private array $sort = [];
    /**
     * @var array<string,Filter>
     */
    private array $filter = [];

    /**
     * @var array<string,Facet>
     */
    private array $facets = [];

    private QueryOperator $defaultQueryOperator =
        QueryOperator::AND;

    public function __construct()
    {
    }

    /**
     * @return $this
     */
    public function index(string $index): static
    {
        if (empty($index)) {
            throw new \InvalidArgumentException('index is empty');
        }
        $this->index = $index;
        return $this;
    }

    /**
     * @return $this
     */
    public function text(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function offset(int $offset): static
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException('offset is lower then 0');
        }
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return $this
     */
    public function limit(int $limit): static
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('limit is lower then 0');
        }
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return $this
     */
    public function sort(Criteria ...$criteriaList): static
    {
        foreach ($criteriaList as $criteria) {
            $this->sort[] = $criteria;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function filter(Filter ...$filterList): static
    {
        foreach ($filterList as $filter) {
            if (isset($this->filter[$filter->key])) {
                throw new \InvalidArgumentException(
                    'filter key "' . $filter->key .
                            '" already exists'
                );
            }
            $this->filter[$filter->key] = $filter;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function facet(Facet ...$facetList): static
    {
        foreach ($facetList as $facet) {
            if (isset($this->facets[$facet->key])) {
                throw new \InvalidArgumentException(
                    'facet key "' . $facet->key .
                    '" already exists'
                );
            }
            $this->facets[$facet->key] = $facet;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function defaultQueryOperator(
        QueryOperator $defaultQueryOperator
    ): static {
        $this->defaultQueryOperator = $defaultQueryOperator;
        return $this;
    }

    public function build(): SelectQuery
    {
        if (empty($this->index)) {
            throw new \InvalidArgumentException('index is not set');
        }
        return new SelectQuery(
            $this->index,
            $this->text,
            $this->offset,
            $this->limit,
            $this->sort,
            array_values($this->filter),
            array_values($this->facets),
            $this->defaultQueryOperator
        );
    }
}
