<?php

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

use Closure;

interface QueryBuilder
{
    /**
     * @see https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#filter-operators
     */
    const OPERATORS = ['=', '!=', 'in', '>=', '<=', '>', '<', 'to', 'not', 'and', 'or'];

    /**
     * @see https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#filter-operators
     */
    const OPERATORS_COLUMN_ONLY = ['exists', 'is empty', 'is null'];

    /**
     * Create a new builder instance for a searchable model.
     */
    public static function for(string $modelClass): self;

    /**
     * Return the Scout builder instance.
     */
    public function search(string $term = ''): \Laravel\Scout\Builder;

    /**
     * Return the compiled query.
     */
    public function compile(): string|self;

    /**
     * Add a where clause to the segments array.
     */
    public function where(
        string|Closure $column,
        mixed $operator = '=',
        mixed $value = null,
        string $boolean = 'AND'
    ): self;
}
