<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery;

use Illuminate\Support\Traits\ForwardsCalls;

final class MeilisearchManager
{
    use ForwardsCalls;

    /** @param list<mixed> $parameters */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->forwardCallTo($this->query(), $method, $parameters);
    }

    public function query(): MeilisearchAdvancedQuery
    {
        return app(MeilisearchAdvancedQuery::class);
    }
}
