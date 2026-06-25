<?php

use Chr15k\MeilisearchAdvancedQuery\MeilisearchAdvancedQuery;
use Chr15k\MeilisearchAdvancedQuery\MeilisearchManager;

it('returns a MeilisearchAdvancedQuery instance', function (): void {
    $manager = app(MeilisearchManager::class);

    expect($manager->query())->toBeInstanceOf(MeilisearchAdvancedQuery::class);
});

it('can call methods on the MeilisearchAdvancedQuery class', function (): void {
    $manager = app(MeilisearchManager::class);

    expect($manager->where('test'))->toBeInstanceOf(MeilisearchAdvancedQuery::class);
});
