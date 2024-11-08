<?php

namespace Chr15k\MeilisearchAdvancedQuery\Tests;

use Chr15k\MeilisearchAdvancedQuery\Facades\FilterBuilder;

final class FilterBuilderTest extends TestCase
{
    public function testCallback()
    {
        $this->assertSame("Closure", get_class(FilterBuilder::where('verified', true)->callback()));
    }

    public function testBasicQuery()
    {
        $compiled = FilterBuilder::where('verified', true)->compile();

        $this->assertSame("verified = 'true'", $compiled);
    }

    public function testBasicQueryWithExplicitOperator()
    {
        $compiled = FilterBuilder::where('verified', '=', true)->compile();

        $this->assertSame("verified = 'true'", $compiled);
    }

    public function testBasicNestedQuery()
    {
        $compiled = FilterBuilder::where(fn ($query) => $query
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
        )->where('verified', true)->compile();

        $this->assertSame("(name = 'Chris' OR name = 'Bob') AND verified = 'true'", $compiled);
    }

    public function testBasicNestedQueryWithExplicitOperator()
    {
        $compiled = FilterBuilder::where(fn ($query) => $query
            ->where('name', '=', 'Chris')
            ->orWhere('name', '=', 'Bob')
        )->where('verified', '=', true)->compile();

        $this->assertSame("(name = 'Chris' OR name = 'Bob') AND verified = 'true'", $compiled);
    }

    public function testBasicWhereInQuery()
    {
        $compiled = FilterBuilder::whereIn('name', ['Chris', 'Bob'])->compile();

        $this->assertSame("name IN ['Chris','Bob']", $compiled);
    }

    public function testBasicWhereInNestedQuery()
    {
        $compiled = FilterBuilder::where(fn ($query) => $query
            ->whereIn('name', ['Chris', 'Bob'])
        )
            ->orWhere('email', 'chris@example.com')
            ->compile();

        $this->assertSame("(name IN ['Chris','Bob']) OR email = 'chris@example.com'", $compiled);
    }

    public function testBasicWhereNotInQuery()
    {
        $compiled = FilterBuilder::whereNotIn('name', ['Chris', 'Bob'])->compile();

        $this->assertSame("name NOT IN ['Chris','Bob']", $compiled);
    }

    public function testBasicWhereNotQuery()
    {
        $compiled = FilterBuilder::whereNot('name', 'Chris')->compile();

        return $this->assertSame("NOT name = 'Chris'", $compiled);
    }

    public function testBasicWhereNotNestedQuery()
    {
        $compiled = FilterBuilder::where(fn ($query) => $query
            ->whereNot('name', 'Chris')
            ->where('email', 'chris@example.com')
        )
            ->orWhere('email', 'bob@example.com')
            ->compile();

        $this->assertSame("(NOT name = 'Chris' AND email = 'chris@example.com') OR email = 'bob@example.com'", $compiled);
    }

    public function testBasicWhereExistsQuery()
    {
        $this->assertSame('name EXISTS', FilterBuilder::whereExists('name')->compile());
    }

    public function testBasicWhereIsNullQuery()
    {
        $this->assertSame('name IS NULL', FilterBuilder::whereIsNull('name')->compile());
    }

    public function testBasicWhereIsEmptyQuery()
    {
        $this->assertSame('name IS EMPTY', FilterBuilder::whereIsEmpty('name')->compile());
    }

    public function testBasicWhereToQuery()
    {
        $this->assertSame('count 1 TO 10', FilterBuilder::whereTo('count', 1, 10)->compile());
    }

    public function testNestedQueryWithoutCompileResolvesToConcreteClass()
    {
        $builder = FilterBuilder::where(fn ($query) => $query
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
        );

        $this->assertInstanceOf(\Chr15k\MeilisearchAdvancedQuery\FilterBuilder::class, $builder);
    }

    public function testCallingCompileFromWithinNestedQueryReturnsFilterBuilderInstance()
    {
        $builder = FilterBuilder::where(fn ($query) => $query
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
            ->compile()
        );

        $this->assertInstanceOf(\Chr15k\MeilisearchAdvancedQuery\FilterBuilder::class, $builder);
    }

    public function testCallingCompileFromWithinNestedQueryDoesNotAffectTheFinalOutput()
    {
        $compiled = FilterBuilder::where(fn ($query) => $query
            ->where('name', 'Chris')
            ->orWhere('name', 'Bob')
            ->compile()
        )
        ->where('verified', true)
        ->compile();

        $this->assertSame("(name = 'Chris' OR name = 'Bob') AND verified = 'true'", $compiled);
    }
}
