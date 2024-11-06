<?php

namespace Chr15k\MeilisearchAdvancedQuery;

use Carbon\Carbon;
use Chr15k\MeilisearchAdvancedQuery\Contracts\FilterSegment;

class Expression implements FilterSegment
{
    const DATE_FORMAT = 'Y-m-d';

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

    protected function and(string $field, mixed $value): string
    {
        return $this->build('AND', $field, $value);
    }

    protected function or(string $field, mixed $value): string
    {
        return $this->build('OR', $field, $value);
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
        $values = collect($array)->map(function ($value) {
            return $this->escape($value);
        })->implode(',');

        return sprintf('%s IN [%s]', $field, $values);
    }

    protected function between(string $field, $from, $to): string
    {
        return sprintf('%s >= %s AND %s <= %s', $field, $from, $field, $to);
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

    protected function getDateFormatted($date)
    {
        if ($date) {
            return Carbon::createFromFormat(self::DATE_FORMAT, $date, 'GMT');
        }
    }

    public function operatorFunc(?string $operator = null): string
    {
        return match (strtolower($operator ?? '')) {
            '=' => 'eq',
            '!=' => 'neq',
            'in' => 'in',
            '>=' => 'gte',
            '<=' => 'lte',
            '>' => 'gt',
            '<' => 'lt',
            'to' => 'to',
            'exists' => 'exists',
            'not' => 'not',
            'and' => 'and',
            'or' => 'or',
            default => 'eq'
        };
    }
}
