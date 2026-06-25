<?php

use Chr15k\MeilisearchAdvancedQuery\Facades\Query;
use Chr15k\MeilisearchAdvancedQuery\MeilisearchAdvancedQuery;

it('can call methods on the MeilisearchAdvancedQuery class', function (): void {
    expect(Query::query())->toBeInstanceOf(MeilisearchAdvancedQuery::class);
});

it('can call methods on the MeilisearchAdvancedQuery class via the facade', function (): void {
    expect(Query::where('test'))->toBeInstanceOf(MeilisearchAdvancedQuery::class);
});
