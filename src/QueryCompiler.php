<?php

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Contracts\QuerySegment;

final class QueryCompiler
{
    public function __invoke(array $segments): string
    {
        return collect($segments)
            ->whereInstanceOf(QuerySegment::class)
            ->map->compile()
            ->unique()
            ->filter()
            ->implode(' ');
    }
}
