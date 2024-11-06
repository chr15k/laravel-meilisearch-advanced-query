<?php

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Contracts\FilterSegment;

final class Filter
{
    public function __invoke(array $segments): string
    {
        return collect($segments)
            ->whereInstanceOf(FilterSegment::class)
            ->map->compile()
            ->unique()
            ->filter()
            ->implode(' ');
    }
}
