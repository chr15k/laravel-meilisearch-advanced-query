<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Adapters\ScoutAdapter;
use Chr15k\MeilisearchAdvancedQuery\Contracts\Compiler;
use Chr15k\MeilisearchAdvancedQuery\Contracts\Node;
use Chr15k\MeilisearchAdvancedQuery\Contracts\Query;
use Chr15k\MeilisearchAdvancedQuery\Enums\BooleanOperator;
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;
use Chr15k\MeilisearchAdvancedQuery\Nodes\BetweenNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\ComparisonNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\GroupNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\InNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\NotInNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\RawNode;
use Closure;

final class MeilisearchAdvancedQuery implements Query
{
    /** @var list<Node> */
    private array $nodeList = [];

    public function __construct(private readonly Compiler $compiler) {}

    public static function query(): static
    {
        return app(self::class);
    }

    public function forModel(string $modelClass): ScoutAdapter
    {
        return ScoutAdapter::for($modelClass, $this);
    }

    /** @return list<Node> */
    public function nodes(): array
    {
        return $this->nodeList;
    }

    public function compile(): string
    {
        return $this->compiler->compileAll($this->nodeList);
    }

    public function where(
        string|Closure $field,
        Operator $operator = Operator::EQ,
        string|int|float|bool|null $value = null,
        BooleanOperator $boolean = BooleanOperator::And,
    ): static {
        if ($field instanceof Closure) {
            $nested = static::newQuery();
            $field($nested);
            $this->nodeList[] = new GroupNode($nested->nodeList, $boolean);

            return $this;
        }

        $this->nodeList[] = new ComparisonNode($field, $operator, $value, $boolean);

        return $this;
    }

    public function orWhere(
        string|Closure $field,
        Operator $operator = Operator::EQ,
        string|int|float|bool|null $value = null,
    ): static {
        return $this->where($field, $operator, $value, BooleanOperator::Or);
    }

    public function whereIn(string $field, array $values): static
    {
        $this->nodeList[] = new InNode($field, $values, BooleanOperator::And);

        return $this;
    }

    public function orWhereIn(string $field, array $values): static
    {
        $this->nodeList[] = new InNode($field, $values, BooleanOperator::Or);

        return $this;
    }

    public function whereNotIn(string $field, array $values): static
    {
        $this->nodeList[] = new NotInNode($field, $values, BooleanOperator::And);

        return $this;
    }

    public function orWhereNotIn(string $field, array $values): static
    {
        $this->nodeList[] = new NotInNode($field, $values, BooleanOperator::Or);

        return $this;
    }

    public function whereNot(string $field, string|int|float|bool|null $value): static
    {
        return $this->where($field, Operator::NOT, $value);
    }

    public function orWhereNot(string $field, string|int|float|bool|null $value): static
    {
        return $this->where($field, Operator::NOT, $value, BooleanOperator::Or);
    }

    public function whereBetween(
        string $field,
        string|int|float|bool|null $from,
        string|int|float|bool|null $to
    ): static {
        $this->nodeList[] = new BetweenNode($field, $from, $to, BooleanOperator::And);

        return $this;
    }

    public function orWhereBetween(
        string $field,
        string|int|float|bool|null $from,
        string|int|float|bool|null $to
    ): static {
        $this->nodeList[] = new BetweenNode($field, $from, $to, BooleanOperator::Or);

        return $this;
    }

    public function whereExists(string $field): static
    {
        return $this->where($field, Operator::EXISTS);
    }

    public function orWhereExists(string $field): static
    {
        return $this->where($field, Operator::EXISTS, null, BooleanOperator::Or);
    }

    public function whereIsNull(string $field): static
    {
        return $this->where($field, Operator::NULL);
    }

    public function orWhereIsNull(string $field): static
    {
        return $this->where($field, Operator::NULL, null, BooleanOperator::Or);
    }

    public function whereIsEmpty(string $field): static
    {
        return $this->where($field, Operator::EMPTY);
    }

    public function orWhereIsEmpty(string $field): static
    {
        return $this->where($field, Operator::EMPTY, null, BooleanOperator::Or);
    }

    public function whereRaw(string $query): static
    {
        $this->nodeList[] = new RawNode($query, BooleanOperator::And);

        return $this;
    }

    public function orWhereRaw(string $query): static
    {
        $this->nodeList[] = new RawNode($query, BooleanOperator::Or);

        return $this;
    }

    public function whereGeoRadius(float $lat, float $lng, float $distanceInMeters): static
    {
        return $this->whereRaw(sprintf('_geoRadius(%s, %s, %s)', $lat, $lng, $distanceInMeters));
    }

    public function orWhereGeoRadius(float $lat, float $lng, float $distanceInMeters): static
    {
        return $this->orWhereRaw(sprintf('_geoRadius(%s, %s, %s)', $lat, $lng, $distanceInMeters));
    }

    public function whereGeoBoundingBox(float $lat1, float $lng1, float $lat2, float $lng2): static
    {
        return $this->whereRaw(sprintf('_geoBoundingBox([%s, %s], [%s, %s])', $lat1, $lng1, $lat2, $lng2));
    }

    public function orWhereGeoBoundingBox(float $lat1, float $lng1, float $lat2, float $lng2): static
    {
        return $this->orWhereRaw(sprintf('_geoBoundingBox([%s, %s], [%s, %s])', $lat1, $lng1, $lat2, $lng2));
    }

    private function newQuery(): static
    {
        return new self($this->compiler);
    }
}
