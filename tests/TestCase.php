<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Tests;

use Chr15k\MeilisearchAdvancedQuery\Facades\FilterBuilder;
use Chr15k\MeilisearchAdvancedQuery\Providers\MeilisearchAdvancedQueryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [MeilisearchAdvancedQueryServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return ['FilterBuilder' => FilterBuilder::class];
    }
}
