<?php

declare(strict_types=1);

use Chr15k\MeilisearchAdvancedQuery\Adapters\ScoutAdapter;
use Chr15k\MeilisearchAdvancedQuery\Compilers\MeilisearchCompiler;
use Chr15k\MeilisearchAdvancedQuery\Contracts\Compiler;
use Chr15k\MeilisearchAdvancedQuery\Contracts\Node;
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;
use Chr15k\MeilisearchAdvancedQuery\MeilisearchQuery;
use Chr15k\MeilisearchAdvancedQuery\Tests\Models\NonSearchableUser;
use Chr15k\MeilisearchAdvancedQuery\Tests\Models\User;
use Laravel\Scout\Builder;

// -------------------------------------------------------------------
// Instantiation / validation
// -------------------------------------------------------------------

describe('ScoutAdapter::for()', function (): void {

    it('creates an instance for a valid searchable model', function (): void {
        $adapter = ScoutAdapter::for(
            User::class,
            MeilisearchQuery::build(),
            new MeilisearchCompiler,
        );

        expect($adapter)->toBeInstanceOf(ScoutAdapter::class);
    });

    it('rejects a non-existent class', function (): void {
        ScoutAdapter::for(
            'App\Models\DoesNotExist',
            MeilisearchQuery::build(),
            new MeilisearchCompiler,
        );
    })->throws(InvalidArgumentException::class);

    it('rejects a non-eloquent class', function (): void {
        ScoutAdapter::for(
            stdClass::class,
            MeilisearchQuery::build(),
            new MeilisearchCompiler,
        );
    })->throws(InvalidArgumentException::class);

    it('rejects a non-searchable eloquent model', function (): void {
        ScoutAdapter::for(
            NonSearchableUser::class,
            MeilisearchQuery::build(),
            new MeilisearchCompiler,
        );
    })->throws(InvalidArgumentException::class);

});

// -------------------------------------------------------------------
// search() returns Scout Builder
// -------------------------------------------------------------------

describe('ScoutAdapter::search()', function (): void {

    it('returns a Scout Builder instance', function (): void {
        $builder = ScoutAdapter::for(
            User::class,
            MeilisearchQuery::build()->where('verified', Operator::EQ, true),
            new MeilisearchCompiler,
        )->search();

        expect($builder)->toBeInstanceOf(Builder::class);
    });

    it('returns a Scout Builder with a search term', function (): void {
        $builder = ScoutAdapter::for(
            User::class,
            MeilisearchQuery::build()->where('verified', Operator::EQ, true),
            new MeilisearchCompiler,
        )->search('Chris');

        expect($builder)->toBeInstanceOf(Builder::class);
    });

});

// -------------------------------------------------------------------
// Compiler contract is respected
// -------------------------------------------------------------------

describe('ScoutAdapter compiler contract', function (): void {

    it('accepts any Compiler implementation', function (): void {
        $compiler = new class implements Compiler
        {
            public function compile(Node $node, bool $isFirst = false): string
            {
                return '';
            }

            public function compileAll(array $nodes): string
            {
                return '';
            }
        };

        $adapter = ScoutAdapter::for(
            User::class,
            MeilisearchQuery::build()->where('name', Operator::EQ, 'Chris'),
            $compiler,
        );

        expect($adapter)->toBeInstanceOf(ScoutAdapter::class);
    });

});

// -------------------------------------------------------------------
// CompilesFilter contract is respected
// -------------------------------------------------------------------

describe('ScoutAdapter query contract', function (): void {

    it('accepts any CompilesFilter implementation', function (): void {
        $query = MeilisearchQuery::build()
            ->where('name', Operator::EQ, 'Chris')
            ->orWhere('name', Operator::EQ, 'Bob');

        $adapter = ScoutAdapter::for(User::class, $query, new MeilisearchCompiler);

        expect($adapter)->toBeInstanceOf(ScoutAdapter::class);
    });

});

describe('ScoutAdapter::callback()', function (): void {

    it('sets filter on options', function (): void {
        $engine = new class
        {
            public string $query = '';

            public array $options = [];

            public function search(string $query, array $options): array
            {
                $this->query = $query;
                $this->options = $options;

                return [];
            }
        };

        $adapter = ScoutAdapter::for(
            User::class,
            MeilisearchQuery::build()->where('verified', Operator::EQ, true),
            new MeilisearchCompiler,
        );

        $callback = $adapter->callback('verified = true', []);
        $callback($engine, 'search term', []);

        expect($engine->options['filter'])->toBe('verified = true');
    });

    it('sets sort on options', function (): void {
        $engine = new class
        {
            public array $options = [];

            public function search(string $query, array $options): array
            {
                $this->options = $options;

                return [];
            }
        };

        $adapter = ScoutAdapter::for(
            User::class,
            MeilisearchQuery::build()->where('verified', Operator::EQ, true),
            new MeilisearchCompiler,
        );

        $callback = $adapter->callback('verified = true', ['name:asc']);
        $callback($engine, 'search term', []);

        expect($engine->options['sort'])->toBe(['name:asc']);
    });

    it('sets both filter and sort on options', function (): void {
        $engine = new class
        {
            public array $options = [];

            public function search(string $query, array $options): array
            {
                $this->options = $options;

                return [];
            }
        };

        $adapter = ScoutAdapter::for(
            User::class,
            MeilisearchQuery::build()
                ->where('verified', Operator::EQ, true)
                ->whereIn('role', ['admin', 'editor']),
            new MeilisearchCompiler,
        );

        $callback = $adapter->callback(
            "verified = true AND role IN ['admin', 'editor']",
            ['name:asc', 'created_at:desc']
        );

        $callback($engine, 'search term', []);

        expect($engine->options['filter'])->toBe("verified = true AND role IN ['admin', 'editor']")
            ->and($engine->options['sort'])->toBe(['name:asc', 'created_at:desc']);
    });

});
