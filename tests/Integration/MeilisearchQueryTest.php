<?php

declare(strict_types=1);

use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;
use Chr15k\MeilisearchAdvancedQuery\MeilisearchAdvancedQuery;

// -------------------------------------------------------------------
// Basic comparisons
// -------------------------------------------------------------------

describe('basic where', function (): void {

    it('compiles a boolean true value', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->where('verified', Operator::EQ, true)->compile()
        )->toBe('verified = true');
    });

    it('compiles a string value', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->where('name', Operator::EQ, 'Chris')->compile()
        )->toBe("name = 'Chris'");
    });

    it('compiles an integer value', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->where('count', Operator::GTE, 10)->compile()
        )->toBe('count >= 10');
    });

    it('compiles greater than or equal', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->where('count', Operator::GTE, 10)->compile()
        )->toBe('count >= 10');
    });

    it('compiles less than or equal', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->where('count', Operator::LTE, 10)->compile()
        )->toBe('count <= 10');
    });

    it('compiles not equal', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->where('email', Operator::NEQ, 'chris@example.com')->compile()
        )->toBe("email != 'chris@example.com'");
    });

    it('compiles NOT operator', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->whereNot('name', 'Chris')->compile()
        )->toBe("NOT name = 'Chris'");
    });

});

// -------------------------------------------------------------------
// IN / NOT IN
// -------------------------------------------------------------------

describe('whereIn / whereNotIn', function (): void {

    it('compiles whereIn', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->whereIn('name', ['Chris', 'Bob'])->compile()
        )->toBe("name IN ['Chris', 'Bob']");
    });

    it('compiles orWhereIn as first node without boolean prefix', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->orWhereIn('name', ['Chris', 'Bob'])->compile()
        )->toBe("name IN ['Chris', 'Bob']");
    });

    it('compiles orWhereIn appended to a comparison node', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where('verified', Operator::EQ, true)
                ->orWhereIn('name', ['Chris', 'Bob'])
                ->compile()
        )->toBe("verified = true OR name IN ['Chris', 'Bob']");
    });

    it('compiles whereNotIn', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->whereNotIn('name', ['Chris', 'Bob'])->compile()
        )->toBe("name NOT IN ['Chris', 'Bob']");
    });

    it('compiles orWhereNotIn as first node without boolean prefix', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->orWhereNotIn('name', ['Chris', 'Bob'])->compile()
        )->toBe("name NOT IN ['Chris', 'Bob']");
    });

    it('compiles orWhereNotIn appended to a comparison node', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where('verified', Operator::EQ, true)
                ->orWhereNotIn('name', ['Chris', 'Bob'])
                ->compile()
        )->toBe("verified = true OR name NOT IN ['Chris', 'Bob']");
    });

    it('compiles whereIn inside a group', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where(fn ($q) => $q->whereIn('name', ['Chris', 'Bob']))
                ->orWhere('email', Operator::EQ, 'chris@example.com')
                ->compile()
        )->toBe("(name IN ['Chris', 'Bob']) OR email = 'chris@example.com'");
    });

    it('compiles whereNotIn inside a group', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where(fn ($q) => $q->whereNotIn('name', ['Chris', 'Bob']))
                ->orWhere('email', Operator::EQ, 'chris@example.com')
                ->compile()
        )->toBe("(name NOT IN ['Chris', 'Bob']) OR email = 'chris@example.com'");
    });

    it('compiles mixed whereIn and whereNotIn', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->whereIn('role', ['admin', 'editor'])
                ->whereNotIn('status', ['banned', 'suspended'])
                ->compile()
        )->toBe("role IN ['admin', 'editor'] AND status NOT IN ['banned', 'suspended']");
    });

});

// -------------------------------------------------------------------
// EXISTS / IS NULL / IS EMPTY
// -------------------------------------------------------------------

describe('unary operators', function (): void {

    it('compiles EXISTS', function (): void {
        expect(MeilisearchAdvancedQuery::query()->whereExists('name')->compile())->toBe('name EXISTS');
    });

    it('compiles IS NULL', function (): void {
        expect(MeilisearchAdvancedQuery::query()->whereIsNull('name')->compile())->toBe('name IS NULL');
    });

    it('compiles IS EMPTY', function (): void {
        expect(MeilisearchAdvancedQuery::query()->whereIsEmpty('name')->compile())->toBe('name IS EMPTY');
    });

    it('compiles orWhereExists as first node without boolean prefix', function (): void {
        expect(MeilisearchAdvancedQuery::query()->orWhereExists('name')->compile())->toBe('name EXISTS');
    });

    it('compiles orWhereIsNull as first node without boolean prefix', function (): void {
        expect(MeilisearchAdvancedQuery::query()->orWhereIsNull('name')->compile())->toBe('name IS NULL');
    });

    it('compiles orWhereIsEmpty as first node without boolean prefix', function (): void {
        expect(MeilisearchAdvancedQuery::query()->orWhereIsEmpty('name')->compile())->toBe('name IS EMPTY');
    });

});

// -------------------------------------------------------------------
// BETWEEN
// -------------------------------------------------------------------

describe('whereBetween', function (): void {

    it('compiles whereBetween', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->whereBetween('count', 1, 10)->compile()
        )->toBe('count 1 TO 10');
    });

    it('compiles orWhereBetween as first node without boolean prefix', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->orWhereBetween('count', 1, 10)->compile()
        )->toBe('count 1 TO 10');
    });

});

// -------------------------------------------------------------------
// Boolean chaining
// -------------------------------------------------------------------

describe('boolean chaining', function (): void {

    it('compiles AND chain', function (): void {
        $compiled = MeilisearchAdvancedQuery::query()
            ->where('name', Operator::EQ, 'Chris')
            ->where('verified', Operator::EQ, true)
            ->compile();

        expect($compiled)->toBe("name = 'Chris' AND verified = true");
    });

    it('compiles OR chain', function (): void {
        $compiled = MeilisearchAdvancedQuery::query()
            ->where('name', Operator::EQ, 'Chris')
            ->orWhere('name', Operator::EQ, 'Bob')
            ->compile();

        expect($compiled)->toBe("name = 'Chris' OR name = 'Bob'");
    });

});

describe('whereNot / orWhereNot', function (): void {

    it('compiles whereNot', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->whereNot('name', 'Chris')->compile()
        )->toBe("NOT name = 'Chris'");
    });

    it('compiles orWhereNot as first node without boolean prefix', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->orWhereNot('name', 'Chris')->compile()
        )->toBe("NOT name = 'Chris'");
    });

    it('compiles orWhereNot appended to a comparison node', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where('verified', Operator::EQ, true)
                ->orWhereNot('name', 'Chris')
                ->compile()
        )->toBe("verified = true OR NOT name = 'Chris'");
    });

    it('compiles whereNot appended to a comparison node', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where('verified', Operator::EQ, true)
                ->whereNot('name', 'Chris')
                ->compile()
        )->toBe("verified = true AND NOT name = 'Chris'");
    });

    it('compiles orWhereNot inside a group', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where(fn ($q) => $q
                    ->where('verified', Operator::EQ, true)
                    ->orWhereNot('name', 'Chris')
                )
                ->compile()
        )->toBe("(verified = true OR NOT name = 'Chris')");
    });

    it('compiles multiple orWhereNot', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->whereNot('name', 'Chris')
                ->orWhereNot('name', 'Bob')
                ->orWhereNot('name', 'Erin')
                ->compile()
        )->toBe("NOT name = 'Chris' OR NOT name = 'Bob' OR NOT name = 'Erin'");
    });

    it('compiles orWhereNot with integer value', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where('count', Operator::GTE, 10)
                ->orWhereNot('count', 0)
                ->compile()
        )->toBe('count >= 10 OR NOT count = 0');
    });

});

// -------------------------------------------------------------------
// Nested / grouped queries
// -------------------------------------------------------------------

describe('nested queries', function (): void {

    it('compiles a basic nested query', function (): void {
        $compiled = MeilisearchAdvancedQuery::query()
            ->where(fn ($q) => $q
                ->where('name', Operator::EQ, 'Chris')
                ->orWhere('name', Operator::EQ, 'Bob')
            )
            ->where('verified', Operator::EQ, true)
            ->compile();

        expect($compiled)->toBe("(name = 'Chris' OR name = 'Bob') AND verified = true");
    });

    it('compiles whereIn inside a nested query', function (): void {
        $compiled = MeilisearchAdvancedQuery::query()
            ->where(fn ($q) => $q->whereIn('name', ['Chris', 'Bob']))
            ->orWhere('email', Operator::EQ, 'chris@example.com')
            ->compile();

        expect($compiled)->toBe("(name IN ['Chris', 'Bob']) OR email = 'chris@example.com'");
    });

    it('compiles whereNot inside a nested query', function (): void {
        $compiled = MeilisearchAdvancedQuery::query()
            ->where(fn ($q) => $q
                ->whereNot('name', 'Chris')
                ->where('email', Operator::EQ, 'chris@example.com')
            )
            ->orWhere('email', Operator::EQ, 'bob@example.com')
            ->compile();

        expect($compiled)->toBe("(NOT name = 'Chris' AND email = 'chris@example.com') OR email = 'bob@example.com'");
    });

    it('compiles multiple nested operators', function (): void {
        $compiled = MeilisearchAdvancedQuery::query()
            ->where(fn ($q) => $q
                ->where('count', Operator::GTE, 10)
                ->where('count', Operator::LTE, 100)
                ->orWhere(fn ($sub) => $sub
                    ->where('name', Operator::EQ, 'Chris')
                    ->orWhereIsEmpty('name')
                    ->orWhereIsNull('email')
                )
            )
            ->orWhere('name', Operator::EQ, 'Bob')
            ->compile();

        expect($compiled)->toBe(
            "(count >= 10 AND count <= 100 OR (name = 'Chris' OR name IS EMPTY OR email IS NULL)) OR name = 'Bob'"
        );
    });

    it('compiles deeply nested queries', function (): void {
        $compiled = MeilisearchAdvancedQuery::query()
            ->where('name', Operator::EQ, 'Chris')
            ->where(fn ($q) => $q
                ->where('name', Operator::EQ, 'Bob')
                ->where('verified', Operator::EQ, true)
            )
            ->orWhere(fn ($q) => $q->where('name', Operator::EQ, 'Erin'))
            ->orWhere(fn ($q) => $q
                ->where('email', Operator::NEQ, 'chris@example.com')
                ->orWhere('email', Operator::EQ, 'test@example.com')
            )
            ->orWhere(fn ($q) => $q->where('email', Operator::EQ, 'erin@example.com'))
            ->compile();

        expect($compiled)->toBe(
            "name = 'Chris' AND (name = 'Bob' AND verified = true) OR (name = 'Erin') OR (email != 'chris@example.com' OR email = 'test@example.com') OR (email = 'erin@example.com')"
        );
    });

    it('returns MeilisearchAdvancedQuery instance before compile', function (): void {
        $builder = MeilisearchAdvancedQuery::query()
            ->where(fn ($q) => $q
                ->where('name', Operator::EQ, 'Chris')
                ->orWhere('name', Operator::EQ, 'Bob')
            );

        expect($builder)->toBeInstanceOf(MeilisearchAdvancedQuery::class);
    });

});

// -------------------------------------------------------------------
// Raw queries
// -------------------------------------------------------------------

describe('raw queries', function (): void {

    it('compiles a single raw query', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->whereRaw("name = 'Chris'")->compile()
        )->toBe("name = 'Chris'");
    });

    it('compiles raw AND chain', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->whereRaw("name = 'Chris'")
                ->whereRaw("name = 'Bob'")
                ->compile()
        )->toBe("name = 'Chris' AND name = 'Bob'");
    });

    it('compiles raw OR chain', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->whereRaw("name = 'Chris'")
                ->orWhereRaw("name = 'Bob'")
                ->compile()
        )->toBe("name = 'Chris' OR name = 'Bob'");
    });

    it('compiles a single raw query with inline OR', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->whereRaw("name = 'Chris' OR name = 'Bob'")
                ->compile()
        )->toBe("name = 'Chris' OR name = 'Bob'");
    });

    it('compiles mixed raw and nested query', function (): void {
        $compiled = MeilisearchAdvancedQuery::query()
            ->where('email', Operator::EQ, 'chris@example.com')
            ->where(fn ($q) => $q
                ->whereRaw("name = 'Chris'")
                ->orWhereRaw("name = 'Bob'")
            )
            ->where('verified', Operator::EQ, true)
            ->compile();

        expect($compiled)->toBe(
            "email = 'chris@example.com' AND (name = 'Chris' OR name = 'Bob') AND verified = true"
        );
    });

});

// -------------------------------------------------------------------
// Geo filters
// -------------------------------------------------------------------

describe('geo filters', function (): void {

    it('compiles whereGeoRadius', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->whereGeoRadius(48.8566, 2.3522, 1000)->compile()
        )->toBe('_geoRadius(48.8566, 2.3522, 1000)');
    });

    it('compiles orWhereGeoRadius', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where('name', Operator::EQ, 'Chris')
                ->orWhereGeoRadius(48.8566, 2.3522, 1000)
                ->compile()
        )->toBe("name = 'Chris' OR _geoRadius(48.8566, 2.3522, 1000)");
    });

    it('compiles whereGeoBoundingBox', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()->whereGeoBoundingBox(48.8566, 2.3522, 48.9, 2.4)->compile()
        )->toBe('_geoBoundingBox([48.8566, 2.3522], [48.9, 2.4])');
    });

    it('compiles orWhereGeoBoundingBox', function (): void {
        expect(
            MeilisearchAdvancedQuery::query()
                ->where('name', Operator::EQ, 'Chris')
                ->orWhereGeoBoundingBox(48.8566, 2.3522, 48.9, 2.4)
                ->compile()
        )->toBe("name = 'Chris' OR _geoBoundingBox([48.8566, 2.3522], [48.9, 2.4])");
    });
});
