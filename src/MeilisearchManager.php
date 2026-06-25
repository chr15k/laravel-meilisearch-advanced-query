<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery;

final class MeilisearchManager
{
    public function __call(string $method, array $arguments): mixed
    {
        return $this->query()->{$method}(...$arguments);
    }

    public function query(): MeilisearchAdvancedQuery
    {
        return app(MeilisearchAdvancedQuery::class);
    }
}
