<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Facades;

use Chr15k\MeilisearchAdvancedQuery\MeilisearchManager;
use Illuminate\Support\Facades\Facade;

final class Query extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MeilisearchManager::class;
    }
}
