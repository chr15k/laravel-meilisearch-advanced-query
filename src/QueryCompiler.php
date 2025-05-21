<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Contracts\QuerySegment;

final class QueryCompiler
{
    public function __invoke(array $segments): string
    {
        return collect($segments)
            ->whereInstanceOf(QuerySegment::class)
            ->map(fn (QuerySegment $segment): string => $segment->compile())
            ->unique()
            ->filter()
            ->implode(' ');
    }
}
