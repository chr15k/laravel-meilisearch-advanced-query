<?php

namespace Chr15k\MeilisearchFilter\Providers;

use Illuminate\Support\ServiceProvider;

class MeilisearchFilterServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('meilisearch-filter.php'),
        ], 'config');
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'meilisearch-filter');
    }
}
