<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Compilers\MeilisearchCompiler;
use Chr15k\MeilisearchAdvancedQuery\Contracts\Compiler;
use Illuminate\Support\ServiceProvider;
use Override;

final class MeilisearchAdvancedQueryServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(Compiler::class, MeilisearchCompiler::class);

        $this->app->bind(MeilisearchAdvancedQuery::class);

        $this->app->singleton(MeilisearchManager::class);
    }
}
