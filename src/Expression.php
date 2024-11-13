<?php

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Contracts\FilterSegment;

class Expression implements FilterSegment
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

    private function build($operator, $field, $value)
    {
        return sprintf("%s {$operator} %s", $field, $this->escape($value));
    }

    protected function gte(string $field, mixed $value): string
    {
        return $this->build('>=', $field, $value);
    }

    protected function lte(string $field, mixed $value): string
    {
        return $this->build('<=', $field, $value);
    }

    protected function eq(string $field, mixed $value): string
    {
        return $this->build('=', $field, $value);
    }

    protected function not(string $field, mixed $value): string
    {
        return 'NOT '.$this->build('=', $field, $value);
    }

    protected function neq(string $field, mixed $value): string
    {
        return $this->build('!=', $field, $value);
    }

    protected function in(string $field, array $array): string
    {
        $values = collect($array)
            ->map(fn ($value) => $this->escape($value))
            ->implode(',');

        return sprintf('%s IN [%s]', $field, $values);
    }

    protected function nin(string $field, array $array): string
    {
        $values = collect($array)
            ->map(fn ($value) => $this->escape($value))
            ->implode(',');

        return sprintf('%s NOT IN [%s]', $field, $values);
    }

    protected function exists(string $field): string
    {
        return sprintf('%s EXISTS', $field);
    }

    protected function null(string $field): string
    {
        return sprintf('%s IS NULL', $field);
    }

    protected function empty(string $field): string
    {
        return sprintf('%s IS EMPTY', $field);
    }

    protected function to(string $field, mixed $value): string
    {
        return sprintf('%s %s TO %s', $field, ...$value);
    }

    protected function bool(string $field, bool $bool): string
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
}
