<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Contracts\QuerySegment;

final class Expression implements QuerySegment
{
    public function __construct(
        public string $column,
        public mixed $value = null,
        public ?string $operator = null,
        public string $boolean = 'AND',
        public bool $init = false
    ) {}

    public function compile(): string
    {
        return trim(sprintf(
            '%s %s', $this->init ? '' : $this->boolean,
            $this->{$this->operatorFunc($this->operator)}($this->column, $this->value)
        ));
    }

    public function operatorFunc(?string $operator = null): string
    {
        return match (strtolower($operator ?? '')) {
            '=' => 'eq',
            '!=' => 'neq',
            'in' => 'in',
            'not in' => 'nin',
            '>=' => 'gte',
            '<=' => 'lte',
            '>' => 'gt',
            '<' => 'lt',
            'to' => 'to',
            'not' => 'not',
            'exists' => 'exists',
            'is empty' => 'empty',
            'is null' => 'null',
            default => 'eq'
        };
    }

    private function build(string $operator, string $field, $value): string
    {
        return sprintf("%s {$operator} %s", $field, $this->escape($value));
    }

    private function gte(string $field, mixed $value): string
    {
        return $this->build('>=', $field, $value);
    }

    private function lte(string $field, mixed $value): string
    {
        return $this->build('<=', $field, $value);
    }

    private function eq(string $field, mixed $value): string
    {
        return $this->build('=', $field, $value);
    }

    private function not(string $field, mixed $value): string
    {
        return 'NOT '.$this->build('=', $field, $value);
    }

    private function neq(string $field, mixed $value): string
    {
        return $this->build('!=', $field, $value);
    }

    private function in(string $field, array $array): string
    {
        $values = collect($array)
            ->map(fn ($value) => $this->escape($value))
            ->implode(',');

        return sprintf('%s IN [%s]', $field, $values);
    }

    private function nin(string $field, array $array): string
    {
        $values = collect($array)
            ->map(fn ($value) => $this->escape($value))
            ->implode(',');

        return sprintf('%s NOT IN [%s]', $field, $values);
    }

    private function exists(string $field): string
    {
        return sprintf('%s EXISTS', $field);
    }

    private function null(string $field): string
    {
        return sprintf('%s IS NULL', $field);
    }

    private function empty(string $field): string
    {
        return sprintf('%s IS EMPTY', $field);
    }

    private function to(string $field, mixed $value): string
    {
        return sprintf('%s %s TO %s', $field, ...$value);
    }

    private function bool(string $field, bool $bool): string
    {
        return sprintf('%s = %s', $field, $this->escape($bool));
    }

    private function escape($data)
    {
        if (is_bool($data)) {
            return $data ? "'true'" : "'false'";
        }

        return $data === null ? 'NULL' : (
            is_int($data) || is_float($data) ? $data : "'{$data}'"
        );
    }
}
