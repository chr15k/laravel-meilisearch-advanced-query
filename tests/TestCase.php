<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Tests;

use Chr15k\MeilisearchAdvancedQuery\MeilisearchAdvancedQueryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MeilisearchAdvancedQueryServiceProvider::class,
        ];
    }
}
