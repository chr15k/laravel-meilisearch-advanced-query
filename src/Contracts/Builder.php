<?php

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

use Closure;

interface Builder
{
    /**
     * @see https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#filter-operators
     */
    const OPERATORS = [
        '=', '!=', 'in', '>=', '<=', '>',
        '<', 'to', 'not', 'and', 'or',
    ];

    /**
     * @see https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#filter-operators
     */
    const OPERATORS_COLUMN_ONLY = [
        'exists', 'is empty', 'is null',
    ];

    /**
     * Return closure for Scout search method.
     *
     * @see https://laravel.com/docs/11.x/scout#customizing-engine-searches
     */
    public function callback(): Closure;

    /**
     * Compile and return the complete filter statement.
     */
    public function compile(): string|self;
}