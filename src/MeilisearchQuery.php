<?php

namespace Chr15k\MeilisearchAdvancedQuery;

use Closure;
use InvalidArgumentException;
use Laravel\Scout\Searchable;
use Meilisearch\Endpoints\Indexes;
use Illuminate\Database\Eloquent\Model;
use Chr15k\MeilisearchAdvancedQuery\Contracts\QueryBuilder;
use Chr15k\MeilisearchAdvancedQuery\Contracts\QuerySegment;

class MeilisearchQuery implements QueryBuilder
{
    /** @var QuerySegment[] */
    protected array $segments = [];

    /** @var null|string[] */
    protected ?array $sort = [];

    /** @var null|Model */
    protected $model;

    private function __construct(public bool $compilable = true) {}

    /**
     * {@inheritDoc}
     */
    public static function for(string $modelClass): self
    {
        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException("The class $modelClass does not exist.");
        }

        $modelInstance = new $modelClass;
        if (! $modelInstance instanceof Model) {
            throw new InvalidArgumentException(
                "The class $modelClass must be an instance of Illuminate\Database\Eloquent\Model."
            );
        }

        if (! in_array(Searchable::class, class_uses_recursive($modelInstance))) {
            throw new InvalidArgumentException("The class $modelClass must be a searchable model.");
        }

        $instance = new self;
        $instance->model = $modelInstance;

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function where(
        string|Closure $column,
        mixed $operator = '=',
        mixed $value = null,
        string $boolean = 'AND'
    ): self {

        $this->ensureModelIsSet();

        if ($column instanceof Closure) {

            $this->segments[] = new NestedExpression(
                $column(new self(false))->segments, $boolean, empty($this->segments)
            );

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
     * {@inheritDoc}
     */
    public function compile(): string|self
    {
        return $this->compilable ? (new QueryCompiler)($this->segments) : $this;
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $term = ''): \Laravel\Scout\Builder
    {
        return $this->model::search($term, $this->callback());
    }

    /**
     * Add a raw "AND" Meilisearch query to the builder.
     */
    public function whereRaw(string $query): self
    {
        return $this->raw($query);
    }

    /**
     * Add a raw "OR" Meilisearch query to the builder.
     */
    public function orWhereRaw(string $query): self
    {
        return $this->raw($query, 'OR');
    }

    /**
     * Add raw expression to the builder.
     */
    protected function raw(string $query, string $boolean = 'AND'): self
    {
        $this->segments[] = new RawExpression($query, $boolean, empty($this->segments));

        return $this;
    }

    /**
     * Ensure that the for() method has been called before proceeding.
     *
     * @throws InvalidArgumentException
     */
    protected function ensureModelIsSet()
    {
        if (! $this->model && $this->compilable) {
            throw new InvalidArgumentException('You must call MeilisearchQuery::for() with a valid model before using this method.');
        }
    }

    /**
     * Return a callback for Scout based on the compiled query.
     */
    protected function callback(): Closure
    {
        $filter = $this->compile();

        return function (Indexes $meilisearch, $query, $options) use ($filter) {
            $options['filter'] = $filter;
            $options['sort'] = $this->sort;

            return $meilisearch->search($query, $options);
        };
    }

    /**
     * Add an "or where" clause to the segments array.
     */
    public function orWhere(string|Closure $column, ?string $operator = null, mixed $value = null): self
    {
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
     * Add a "OR where IN" clause to the segments array.
     */
    public function orWhereIn(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'IN', $value, 'OR');
    }

    /**
     * Add a "where NOT IN" clause to the segments array.
     */
    public function whereNotIn(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'NOT IN', $value);
    }

    /**
     * Add a "OR where NOT IN" clause to the segments array.
     */
    public function orWhereNotIn(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'NOT IN', $value, 'OR');
    }

    /**
     * Add a "where NOT" clause to the segments array.
     */
    public function whereNot(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'NOT', $value);
    }

    /**
     * Add a "OR where NOT" clause to the segments array.
     */
    public function orWhereNot(string|Closure $column, mixed $value = null): self
    {
        return $this->where($column, 'NOT', $value, 'OR');
    }

    /**
     * Add a "where EXISTS" clause to the segments array.
     */
    public function whereExists(string|Closure $column): self
    {
        return $this->where($column, 'EXISTS');
    }

    /**
     * Add a "OR where EXISTS" clause to the segments array.
     */
    public function orWhereExists(string|Closure $column): self
    {
        return $this->where($column, 'EXISTS', null, 'OR');
    }

    /**
     * Add a "where IS NULL" clause to the segments array.
     */
    public function whereIsNull(string|Closure $column): self
    {
        return $this->where($column, 'IS NULL');
    }

    /**
     * Add a "OR where IS NULL" clause to the segments array.
     */
    public function orWhereIsNull(string|Closure $column): self
    {
        return $this->where($column, 'IS NULL', null, 'OR');
    }

    /**
     * Add a "where IS EMPTY" clause to the segments array.
     */
    public function whereIsEmpty(string|Closure $column): self
    {
        return $this->where($column, 'IS EMPTY');
    }

    /**
     * Add a "OR where IS EMPTY" clause to the segments array.
     */
    public function orWhereIsEmpty(string|Closure $column): self
    {
        return $this->where($column, 'IS EMPTY', null, 'OR');
    }

    /**
     * Add a "where TO" clause to the segments array.
     */
    public function whereTo(string|Closure $column, mixed $from, mixed $to): self
    {
        return $this->where($column, 'TO', [$from, $to]);
    }

    /**
     * Add a "OR where TO" clause to the segments array.
     */
    public function orWhereTo(string|Closure $column, mixed $from, mixed $to): self
    {
        return $this->where($column, 'TO', [$from, $to], 'OR');
    }

    /**
     * Add a sort clause to the builder instance.
     */
    public function sort(array|string $sort): self
    {
        $this->ensureModelIsSet();

        $this->sort = (array) $sort;

        return $this;
    }

    /**
     * Inspect the builder properties.
     */
    public function inspect(): array
    {
        return [
            'segments' => $this->segments,
            'sort' => $this->sort,
            'model' => $this->model
        ];
    }

    /**
     * Dump the builder properties.
     */
    public function dump()
    {
        dump($this->inspect());
    }

    /**
     * Prepare the value and operator for a where clause.
     */
    protected function prepareValueAndOperator(mixed $value, mixed $operator, bool $useDefault = false): array
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
    protected function shouldUseDefaultValueAndOperator(int $argCount, ?string $operator): bool
    {
        return $argCount === 2 && ! in_array(strtolower($operator ?? ''), self::OPERATORS_COLUMN_ONLY);
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     */
    protected function invalidOperatorAndValue(?string $operator, mixed $value): bool
    {
        return is_null($value) && in_array(strtolower($operator ?? ''), self::OPERATORS);
    }
}
