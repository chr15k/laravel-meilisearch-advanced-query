<?php

namespace Chr15k\MeilisearchFilter\Tests;

use Chr15k\MeilisearchFilter\Facades\FilterBuilder;

final class FilterBuilderTest extends TestCase
{
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
}
