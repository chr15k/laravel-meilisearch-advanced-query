<?php

namespace Chr15k\MeilisearchFilter;

use Closure;
use InvalidArgumentException;

class FilterBuilder
{
    /**
     * @var Expression|Nested[]
     */
    public array $segments = [];

    /**
     * All of the available clause operators.
     *
     * @var string[]
     */
    public array $operators = [
        '=', '!=', 'in', '>=', '<=', '>',
        '<', 'to', 'exists', 'not', 'and', 'or',
    ];

    /**
     * Compile and return the complete filter statement.
     */
    public function compile(): string
    {
        return (new Filter)($this->segments);
    }

    /**
     * Add an "or where" clause to the segments array.
     */
    public function orWhere(
        string|Closure $column,
        ?string $operator = null,
        mixed $value = null,
    ): self {

        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add a "where IN" clause to the segments array.
     */
    public function whereIn(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'IN', $value);
    }

    /**
     * Add a "where NOT" clause to the segments array.
     */
    public function whereNot(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'NOT', $value);
    }

    /**
     * Add a where clause to the segments array.
     */
    public function where(
        string|Closure $column,
        mixed $operator = '=',
        mixed $value = null,
        string $boolean = 'AND'
    ): self {

        if ($column instanceof Closure) {
            $this->segments[] = new Nested($column(new self)->segments);

            return $this;
        }

        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->segments[] = new Expression(
            $column, $value, $operator, $boolean, empty($this->segments)
        );

        return $this;
    }

    /**
     * Prepare the value and operator for a where clause.
     */
    public function prepareValueAndOperator(mixed $value, mixed $operator, bool $useDefault = false): array
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     */
    protected function invalidOperatorAndValue(string $operator, mixed $value): bool
    {
        return is_null($value) && in_array(strtolower($operator ?? ''), $this->operators);
    }
}
