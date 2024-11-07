<?php

namespace Chr15k\MeilisearchAdvancedQuery;

use Closure;
use InvalidArgumentException;
use Meilisearch\Endpoints\Indexes;

class FilterBuilder
{
    /**
     * @var Expression|Nested[]
     */
    public array $segments = [];

    const DEFAULT_LIMIT = 20;

    public int $limit = self::DEFAULT_LIMIT;
    public ?string $sort;
    public ?string $query;

    /**
     * @var string[]
     */
    public array $operators = [
        '=', '!=', 'in', '>=', '<=', '>',
        '<', 'to', 'not', 'and', 'or',
    ];

    /**
     * @var string[]
     */
    public array $columnOnlyOperators = [
        'exists', 'is empty', 'is null',
    ];

    public function callback()
    {
        return function (Indexes $meilisearch) {
            return $meilisearch->search($this->query, [
                'filter' => $this->compile(),
                'sort' => $this->sort,
                'limit' => $this->limit
            ]);
        };
    }

    /**
     * Compile and return the complete filter statement.
     */
    public function compile(): string|self
    {
        // If called from within a closure, return the instance.
        if (str_contains(debug_backtrace(
            DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'], '{closure}'
        )) {
            return $this;
        }

        return (new Filter)($this->segments);
    }

    public function limit(int $limit = self::DEFAULT_LIMIT)
    {
        $this->limit = $limit;
    }

    public function sort(?string $sort = null)
    {
        $this->sort = $sort;
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
            $value, $operator, $this->shouldUseDefaultValueAndOperator(func_num_args(), $operator)
        );

        $this->segments[] = new Expression(
            $column, $value, $operator, $boolean, empty($this->segments)
        );

        return $this;
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
            $value, $operator, $this->shouldUseDefaultValueAndOperator(func_num_args(), $operator)
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
     * Add a "where NOT IN" clause to the segments array.
     */
    public function whereNotIn(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'NOT IN', $value);
    }

    /**
     * Add a "where NOT" clause to the segments array.
     */
    public function whereNot(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'NOT', $value);
    }

    /**
     * Add a "where EXISTS" clause to the segments array.
     */
    public function whereExists(string|Closure $column): self
    {
        return $this->where($column, 'EXISTS');
    }

    /**
     * Add a "where IS NULL" clause to the segments array.
     */
    public function whereIsNull(string|Closure $column): self
    {
        return $this->where($column, 'IS NULL');
    }

    /**
     * Add a "where IS EMPTY" clause to the segments array.
     */
    public function whereIsEmpty(string|Closure $column): self
    {
        return $this->where($column, 'IS EMPTY');
    }

    /**
     * Add a "where TO clause to the segments array.
     */
    public function whereTo(string|Closure $column, mixed ...$value): self
    {
        return $this->where($column, 'TO', $value);
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
     * Determine if the given operator and arg count combination should use the default operator and value.
     */
    public function shouldUseDefaultValueAndOperator(int $argCount, string $operator): bool
    {
        return $argCount === 2 && ! in_array(strtolower($operator), $this->columnOnlyOperators);
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     */
    protected function invalidOperatorAndValue(string $operator, mixed $value): bool
    {
        return is_null($value) && in_array(strtolower($operator), $this->operators);
    }
}
