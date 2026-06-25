<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

use Chr15k\MeilisearchAdvancedQuery\Enums\BooleanOperator;
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;
use Closure;

interface Query
{
    /** @return list<Node> */
    public function nodes(): array;

    public function compile(): string;

    public function where(
        string|Closure $field,
        Operator $operator = Operator::EQ,
        string|int|float|bool|null $value = null,
        BooleanOperator $boolean = BooleanOperator::And,
    ): static;

    public function orWhere(
        string|Closure $field,
        Operator $operator = Operator::EQ,
        string|int|float|bool|null $value = null,
    ): static;

    public function whereRaw(string $query): static;

    public function orWhereRaw(string $query): static;

    /** @param  list<string>  $values */
    public function whereIn(string $field, array $values): static;

    /** @param  list<string>  $values */
    public function orWhereIn(string $field, array $values): static;

    /** @param  list<string>  $values */
    public function whereNotIn(string $field, array $values): static;

    /** @param  list<string>  $values */
    public function orWhereNotIn(string $field, array $values): static;

    public function whereNot(string $field, string|int|float|bool|null $value): static;

    public function orWhereNot(string $field, string|int|float|bool|null $value): static;

    public function whereBetween(string $field, string|int|float|bool|null $from, string|int|float|bool|null $to): static;

    public function orWhereBetween(string $field, string|int|float|bool|null $from, string|int|float|bool|null $to): static;

    public function whereExists(string $field): static;

    public function orWhereExists(string $field): static;

    public function whereIsNull(string $field): static;

    public function orWhereIsNull(string $field): static;

    public function whereIsEmpty(string $field): static;

    public function orWhereIsEmpty(string $field): static;

    public function whereGeoRadius(float $lat, float $lng, float $distanceInMeters): static;

    public function orWhereGeoRadius(float $lat, float $lng, float $distanceInMeters): static;

    public function whereGeoBoundingBox(float $lat1, float $lng1, float $lat2, float $lng2): static;

    public function orWhereGeoBoundingBox(float $lat1, float $lng1, float $lat2, float $lng2): static;
}
