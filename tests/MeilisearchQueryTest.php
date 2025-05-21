<?php

namespace Chr15k\MeilisearchAdvancedQuery\Tests;

use InvalidArgumentException;
use Chr15k\MeilisearchAdvancedQuery\MeilisearchQuery;
use Chr15k\MeilisearchAdvancedQuery\Tests\Models\NonSearchableUser;
use Chr15k\MeilisearchAdvancedQuery\Tests\Models\User;

final class MeilisearchQueryTest extends TestCase
{
    public function testForEloquentModel()
    {
        $this->assertSame(
            MeilisearchQuery::class,
            get_class(MeilisearchQuery::for(User::class))
        );
    }

    public function testForNonEloquentModel()
    {
        $this->expectException(InvalidArgumentException::class);
        MeilisearchQuery::for(\stdClass::class);
    }

    public function testForNonSearchableEloquentModel()
    {
        $this->expectException(InvalidArgumentException::class);
        MeilisearchQuery::for(NonSearchableUser::class);
    }

    public function testBasicQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->where('verified', true)->compile();

        $this->assertSame("verified = 'true'", $compiled);
    }

    public function testBasicQueryWithExplicitOperator()
    {
        $compiled = MeilisearchQuery::for(User::class)->where('verified', '=', true)->compile();

        $this->assertSame("verified = 'true'", $compiled);
    }

    public function testBasicNestedQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
        )->where('verified', true)->compile();

        $this->assertSame("(name = 'Chris' OR name = 'Bob') AND verified = 'true'", $compiled);
    }

    public function testBasicNestedQueryWithExplicitOperator()
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->where('name', '=', 'Chris')
            ->orWhere('name', '=', 'Bob')
        )->where('verified', '=', true)->compile();

        $this->assertSame("(name = 'Chris' OR name = 'Bob') AND verified = 'true'", $compiled);
    }

    public function testBasicWhereInQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->whereIn('name', ['Chris', 'Bob'])->compile();

        $this->assertSame("name IN ['Chris','Bob']", $compiled);
    }

    public function testBasicOrWhereInQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->orWhereIn('name', ['Chris', 'Bob'])->compile();

        $this->assertSame("name IN ['Chris','Bob']", $compiled);
    }

    public function testBasicWhereInNestedQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->whereIn('name', ['Chris', 'Bob'])
        )
            ->orWhere('email', 'chris@example.com')
            ->compile();

        $this->assertSame("(name IN ['Chris','Bob']) OR email = 'chris@example.com'", $compiled);
    }

    public function testBasicWhereNotInQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->whereNotIn('name', ['Chris', 'Bob'])->compile();

        $this->assertSame("name NOT IN ['Chris','Bob']", $compiled);
    }

    public function testBasicWhereNotQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->whereNot('name', 'Chris')->compile();

        return $this->assertSame("NOT name = 'Chris'", $compiled);
    }

    public function testBasicWhereNotNestedQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->whereNot('name', 'Chris')
            ->where('email', 'chris@example.com')
        )
            ->orWhere('email', 'bob@example.com')
            ->compile();

        $this->assertSame("(NOT name = 'Chris' AND email = 'chris@example.com') OR email = 'bob@example.com'", $compiled);
    }

    public function testBasicWhereExistsQuery()
    {
        $this->assertSame('name EXISTS', MeilisearchQuery::for(User::class)->whereExists('name')->compile());
    }

    public function testBasicWhereIsNullQuery()
    {
        $this->assertSame('name IS NULL', MeilisearchQuery::for(User::class)->whereIsNull('name')->compile());
    }

    public function testBasicWhereIsEmptyQuery()
    {
        $this->assertSame('name IS EMPTY', MeilisearchQuery::for(User::class)->whereIsEmpty('name')->compile());
    }

    public function testBasicOrWhereExistsQuery()
    {
        $this->assertSame('name EXISTS', MeilisearchQuery::for(User::class)->orWhereExists('name')->compile());
    }

    public function testBasicOrWhereIsNullQuery()
    {
        $this->assertSame('name IS NULL', MeilisearchQuery::for(User::class)->orWhereIsNull('name')->compile());
    }

    public function testBasicOrWhereIsEmptyQuery()
    {
        $this->assertSame('name IS EMPTY', MeilisearchQuery::for(User::class)->orWhereIsEmpty('name')->compile());
    }

    public function testBasicOrWhereToQuery()
    {
        $this->assertSame('count 1 TO 10', MeilisearchQuery::for(User::class)->orWhereTo('count', 1, 10)->compile());
    }

    public function testBasicWhereGteQuery()
    {
        $this->assertSame('count >= 10', MeilisearchQuery::for(User::class)->where('count', ">=", 10)->compile());
    }

    public function testBasicWhereLteQuery()
    {
        $this->assertSame('count <= 10', MeilisearchQuery::for(User::class)->where('count', "<=", 10)->compile());
    }

    public function testMultipleNestedOperators()
    {
        $compiled = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->where('count', ">=", 10)
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

    public function testNestedInception()
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

    public function testNestedQueryWithoutCompileResolvesToConcreteClass()
    {
        $builder = MeilisearchQuery::for(User::class)->where(fn ($query) => $query
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
        );

        $this->assertInstanceOf(\Chr15k\MeilisearchAdvancedQuery\MeilisearchQuery::class, $builder);
    }

    public function testSingleSort()
    {
        $instance = MeilisearchQuery::for(User::class)
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
            ->sort('name:desc')
            ->inspect();

        $this->assertSame(['name:desc'], $instance['sort']);
    }

    public function testMultipleSort()
    {
        $instance = MeilisearchQuery::for(User::class)
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
            ->sort(['name:desc', 'email:asc'])
            ->inspect();

        $this->assertSame(['name:desc', 'email:asc'], $instance['sort']);
    }

    public function testBasicRawQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)->whereRaw("name = 'Chris'")->compile();

        $this->assertSame("name = 'Chris'", $compiled);
    }

    public function testBasicMultipleRawOrQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereRaw("name = 'Chris'")
            ->orWhereRaw("name = 'Bob'")
            ->compile();

        $this->assertSame("name = 'Chris' OR name = 'Bob'", $compiled);
    }

    public function testBasicRawAndQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereRaw("name = 'Chris'")
            ->whereRaw("name = 'Bob'")
            ->compile();

        $this->assertSame("name = 'Chris' AND name = 'Bob'", $compiled);
    }

    public function testSingleBasicRawQuery()
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereRaw("name = 'Chris' OR name = 'Bob'")
            ->compile();

        $this->assertSame("name = 'Chris' OR name = 'Bob'", $compiled);
    }

    public function testMixedRawNestedQuery()
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

    public function testWhereGeoRadius()
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereGeoRadius(48.8566, 2.3522, 1000)
            ->compile();

        $this->assertSame("_geoRadius(48.8566, 2.3522, 1000)", $compiled);
    }

    public function testOrWhereGeoRadius()
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->where('name', 'Chris')
            ->orWhereGeoRadius(48.8566, 2.3522, 1000)
            ->compile();

        $this->assertSame("name = 'Chris' OR _geoRadius(48.8566, 2.3522, 1000)", $compiled);
    }

    public function testWhereGeoBoundingBox()
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->whereGeoBoundingBox(48.8566, 2.3522, 48.9, 2.4)
            ->compile();

        $this->assertSame("_geoBoundingBox([48.8566, 2.3522], [48.9, 2.4])", $compiled);
    }

    public function testOrWhereGeoBoundingBox()
    {
        $compiled = MeilisearchQuery::for(User::class)
            ->where('name', 'Chris')
            ->orWhereGeoBoundingBox(48.8566, 2.3522, 48.9, 2.4)
            ->compile();

        $this->assertSame("name = 'Chris' OR _geoBoundingBox([48.8566, 2.3522], [48.9, 2.4])", $compiled);
    }
}
