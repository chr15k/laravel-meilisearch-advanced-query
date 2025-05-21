<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Tests;

use Chr15k\MeilisearchAdvancedQuery\MeilisearchQuery;
use Chr15k\MeilisearchAdvancedQuery\Tests\Models\NonSearchableUser;
use Chr15k\MeilisearchAdvancedQuery\Tests\Models\User;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use stdClass;

final class MeilisearchQueryTest extends TestCase
{
    public function test_for_eloquent_model(): void
    {
        $this->assertSame(
            MeilisearchQuery::class,
            MeilisearchQuery::for(User::class)::class
        );
    }

    public function test_for_non_eloquent_model(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MeilisearchQuery::for(stdClass::class);
    }

    public function test_for_non_searchable_eloquent_model(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MeilisearchQuery::for(NonSearchableUser::class);
    }

    public function test_basic_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->where('verified', true)->compile();

        $this->assertSame("verified = 'true'", $compiled);
    }

    public function test_basic_query_with_explicit_operator(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->where('verified', '=', true)->compile();

        $this->assertSame("verified = 'true'", $compiled);
    }

    public function test_basic_nested_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
        )->where('verified', true)->compile();

        $this->assertSame("(name = 'Chris' OR name = 'Bob') AND verified = 'true'", $compiled);
    }

    public function test_basic_nested_query_with_explicit_operator(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->where('name', '=', 'Chris')
            ->orWhere('name', '=', 'Bob')
        )->where('verified', '=', true)->compile();

        $this->assertSame("(name = 'Chris' OR name = 'Bob') AND verified = 'true'", $compiled);
    }

    public function test_basic_where_in_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->whereIn('name', ['Chris', 'Bob'])->compile();

        $this->assertSame("name IN ['Chris','Bob']", $compiled);
    }

    public function test_basic_or_where_in_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->orWhereIn('name', ['Chris', 'Bob'])->compile();

        $this->assertSame("name IN ['Chris','Bob']", $compiled);
    }

    public function test_basic_where_in_nested_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->whereIn('name', ['Chris', 'Bob'])
        )
            ->orWhere('email', 'chris@example.com')
            ->compile();

        $this->assertSame("(name IN ['Chris','Bob']) OR email = 'chris@example.com'", $compiled);
    }

    public function test_basic_where_not_in_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->whereNotIn('name', ['Chris', 'Bob'])->compile();

        $this->assertSame("name NOT IN ['Chris','Bob']", $compiled);
    }

    public function test_basic_where_not_query()
    {
        $compiled = MeilisearchQuery::for(User::class)->whereNot('name', 'Chris')->compile();

        return $this->assertSame("NOT name = 'Chris'", $compiled);
    }

    public function test_basic_where_not_nested_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->whereNot('name', 'Chris')
            ->where('email', 'chris@example.com')
        )
            ->orWhere('email', 'bob@example.com')
            ->compile();

        $this->assertSame("(NOT name = 'Chris' AND email = 'chris@example.com') OR email = 'bob@example.com'", $compiled);
    }

    public function test_basic_where_exists_query(): void
    {
        $this->assertSame('name EXISTS', MeilisearchQuery::for(User::class)->whereExists('name')->compile());
    }

    public function test_basic_where_is_null_query(): void
    {
        $this->assertSame('name IS NULL', MeilisearchQuery::for(User::class)->whereIsNull('name')->compile());
    }

    public function test_basic_where_is_empty_query(): void
    {
        $this->assertSame('name IS EMPTY', MeilisearchQuery::for(User::class)->whereIsEmpty('name')->compile());
    }

    public function test_basic_or_where_exists_query(): void
    {
        $this->assertSame('name EXISTS', MeilisearchQuery::for(User::class)->orWhereExists('name')->compile());
    }

    public function test_basic_or_where_is_null_query(): void
    {
        $this->assertSame('name IS NULL', MeilisearchQuery::for(User::class)->orWhereIsNull('name')->compile());
    }

    public function test_basic_or_where_is_empty_query(): void
    {
        $this->assertSame('name IS EMPTY', MeilisearchQuery::for(User::class)->orWhereIsEmpty('name')->compile());
    }

    public function test_basic_or_where_to_query(): void
    {
        $this->assertSame('count 1 TO 10', MeilisearchQuery::for(User::class)->orWhereTo('count', 1, 10)->compile());
    }

    public function test_basic_where_gte_query(): void
    {
        $this->assertSame('count >= 10', MeilisearchQuery::for(User::class)->where('count', '>=', 10)->compile());
    }

    public function test_basic_where_lte_query(): void
    {
        $this->assertSame('count <= 10', MeilisearchQuery::for(User::class)->where('count', '<=', 10)->compile());
    }

    public function test_multiple_nested_operators(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->where('count', '>=', 10)
            ->where('count', '<=', 100)
            ->orWhere(fn ($subQuery) => $subQuery
                ->where('name', 'Chris')
                ->orWhereIsEmpty('name')
                ->orWhereIsNull('email')
            )
        )
            ->orWhere('name', 'Bob')
            ->compile();

        $this->assertSame("(count >= 10 AND count <= 100 OR (name = 'Chris' OR name IS EMPTY OR email IS NULL)) OR name = 'Bob'", $compiled);
    }

    public function test_nested_inception(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->where('name', 'Chris')
            ->where(fn ($query) => $query->where('name', 'Bob')->where('verified', true))
            ->orWhere(fn ($query) => $query->where('name', 'Erin'))
            ->orWhere(fn ($query) => $query->where('email', '!=', 'chris@example.com')
                ->orWhere('email', 'test@example.com'))
            ->orWhere(fn ($query) => $query->where('email', 'erin@example.com'))
            ->compile();

        $this->assertSame(
            "name = 'Chris' AND (name = 'Bob' AND verified = 'true') OR (name = 'Erin') OR (email != 'chris@example.com' OR email = 'test@example.com') OR (email = 'erin@example.com')",
            $compiled
        );
    }

    public function test_nested_query_without_compile_resolves_to_concrete_class(): void
    {
        $builder = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
        );

        $this->assertInstanceOf(MeilisearchQuery::class, $builder);
    }

    public function test_single_sort(): void
    {
        $instance = MeilisearchQuery::for(User::class)
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
            ->sort('name:desc')
            ->inspect();

        $this->assertSame(['name:desc'], $instance['sort']);
    }

    public function test_multiple_sort(): void
    {
        $instance = MeilisearchQuery::for(User::class)
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
            ->sort(['name:desc', 'email:asc'])
            ->inspect();

        $this->assertSame(['name:desc', 'email:asc'], $instance['sort']);
    }

    public function test_basic_raw_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)->whereRaw("name = 'Chris'")->compile();

        $this->assertSame("name = 'Chris'", $compiled);
    }

    public function test_basic_multiple_raw_or_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereRaw("name = 'Chris'")
            ->orWhereRaw("name = 'Bob'")
            ->compile();

        $this->assertSame("name = 'Chris' OR name = 'Bob'", $compiled);
    }

    public function test_basic_raw_and_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereRaw("name = 'Chris'")
            ->whereRaw("name = 'Bob'")
            ->compile();

        $this->assertSame("name = 'Chris' AND name = 'Bob'", $compiled);
    }

    public function test_single_basic_raw_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereRaw("name = 'Chris' OR name = 'Bob'")
            ->compile();

        $this->assertSame("name = 'Chris' OR name = 'Bob'", $compiled);
    }

    public function test_mixed_raw_nested_query(): void
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->where('email', 'chris@example.com')
            ->where(fn ($query) => $query
                ->whereRaw("name = 'Chris'")
                ->orWhereRaw("name = 'Bob'")
            )
            ->where('verified', true)
            ->compile();

        $this->assertSame("email = 'chris@example.com' AND (name = 'Chris' OR name = 'Bob') AND verified = 'true'", $compiled);
    }

    public function test_where_geo_radius(): void
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereGeoRadius(48.8566, 2.3522, 1000)
            ->compile();

        $this->assertSame('_geoRadius(48.8566, 2.3522, 1000)', $compiled);
    }

    public function test_or_where_geo_radius(): void
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->where('name', 'Chris')
            ->orWhereGeoRadius(48.8566, 2.3522, 1000)
            ->compile();

        $this->assertSame("name = 'Chris' OR _geoRadius(48.8566, 2.3522, 1000)", $compiled);
    }

    public function test_where_geo_bounding_box(): void
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereGeoBoundingBox(48.8566, 2.3522, 48.9, 2.4)
            ->compile();

        $this->assertSame('_geoBoundingBox([48.8566, 2.3522], [48.9, 2.4])', $compiled);
    }

    public function test_or_where_geo_bounding_box(): void
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->where('name', 'Chris')
            ->orWhereGeoBoundingBox(48.8566, 2.3522, 48.9, 2.4)
            ->compile();

        $this->assertSame("name = 'Chris' OR _geoBoundingBox([48.8566, 2.3522], [48.9, 2.4])", $compiled);
    }
}
