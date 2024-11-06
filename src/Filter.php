<?php

namespace Chr15k\MeilisearchFilter;

use Chr15k\MeilisearchFilter\Contracts\FilterSegment;

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
