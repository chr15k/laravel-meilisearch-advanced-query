<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchFilter\Tests;

use Chr15k\MeilisearchFilter\Facades\FilterBuilder;
use Chr15k\MeilisearchFilter\Providers\MeilisearchFilterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [MeilisearchFilterServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return ['FilterBuilder' => FilterBuilder::class];
    }
}
