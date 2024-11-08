<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Tests;

use Chr15k\MeilisearchAdvancedQuery\Facades\FilterBuilder;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageAliases($app)
    {
        return ['FilterBuilder' => FilterBuilder::class];
    }
}
