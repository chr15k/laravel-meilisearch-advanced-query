<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

interface SearchAdapter
{
    /**
     * @param  list<string>  $sort
     * @return Builder<Model>
     */
    public function search(string $term = '', array $sort = []): Builder;

    /** @param list<string> $sort */
    public function callback(string $filter, array $sort): Closure;
}
