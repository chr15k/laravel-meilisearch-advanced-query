<picture>
    <source media="(prefers-color-scheme: dark)" srcset="art/header-dark.svg">
    <img alt="Logo for php typos" src="art/header-light.svg">
</picture>

<p align="center">
    <p align="center">
        <a href="https://github.com/chr15k/laravel-meilisearch-advanced-query/actions"><img alt="GitHub Workflow Status (master)" src="https://img.shields.io/github/actions/workflow/status/chr15k/laravel-meilisearch-advanced-query/main.yml"></a>
        <a href="https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/chr15k/laravel-meilisearch-advanced-query"></a>
        <a href="https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query"><img alt="Latest Version" src="https://img.shields.io/packagist/v/chr15k/laravel-meilisearch-advanced-query"></a>
        <a href="https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query"><img alt="License" src="https://img.shields.io/github/license/chr15k/laravel-meilisearch-advanced-query"></a>
    </p>
</p>

------

**Meilisearch Advanced Query** provides a fluent, expressive API for building Meilisearch filter
expressions in Laravel — the same filters you'd otherwise write by hand when
[customising Scout engine searches](https://laravel.com/docs/13.x/scout#customizing-engine-searches).

It handles compound conditions, nested groups, range queries, geo filters, and
Meilisearch-specific operators, keeping your code readable as queries grow in complexity.

> [!WARNING]
> v3 contains breaking changes. If you are upgrading from v2, see the [upgrade guide](UPGRADE.md).

---

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- Laravel Scout 11+
- Meilisearch PHP SDK 1.16+

---

## Installation

```bash
composer require chr15k/laravel-meilisearch-advanced-query
```

The service provider is registered automatically via Laravel's package discovery.

---

## Usage

### Building a filter string

Use the `Query` facade (or resolve `MeilisearchAdvancedQuery` from the container directly) to build a filter string without touching Scout at all:

```php
use Chr15k\MeilisearchAdvancedQuery\Facades\Query;
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;

$filter = Query::where('status', Operator::EQ, 'active')
    ->whereIn('role', ['admin', 'editor'])
    ->whereBetween('login_count', 10, 500)
    ->compile();

// "status = 'active' AND role IN ['admin', 'editor'] AND login_count 10 TO 500"
```

### Running a Scout search

Chain `forModel()` on the query builder to hand off to Scout. This returns a Scout `Builder` instance that you can continue to chain as normal:

```php
use Chr15k\MeilisearchAdvancedQuery\Facades\Query;
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;
use App\Models\Product;

$results = Query::where('status', Operator::EQ, 'active')
    ->whereIn('category', ['boots', 'shoes'])
    ->forModel(Product::class)
    ->search('leather')
    ->paginate(20);
```

### Sorting

Pass a sort expression (or array of expressions) to `search()`:

```php
Query::where('status', Operator::EQ, 'active')
    ->forModel(Product::class)
    ->search('leather', sort: ['price:asc', 'name:desc']);
```

For Meilisearch's sort syntax, see the [sorting documentation](https://www.meilisearch.com/docs/learn/filtering_and_sorting/sort_search_results).

---

## The `Query` Facade

The `Query` facade proxies to a fresh `MeilisearchAdvancedQuery` instance on each call, so there is no shared state between requests.

```php
use Chr15k\MeilisearchAdvancedQuery\Facades\Query;
```

You can also resolve the builder from the container directly if you prefer:

```php
use Chr15k\MeilisearchAdvancedQuery\MeilisearchAdvancedQuery;

$query = app(MeilisearchAdvancedQuery::class);
```

---

## Builder Methods

### `where(field, operator, value, boolean)`

The primary method. Operator defaults to `Operator::EQ`. Boolean defaults to `AND`.

```php
Query::where('name', Operator::EQ, 'Chris')->compile();
// "name = 'Chris'"

Query::where('count', Operator::GTE, 10)->compile();
// "count >= 10"

Query::where('verified', Operator::EQ, true)->compile();
// "verified = true"
```

### `orWhere(field, operator, value)`

Identical to `where()` but joins with `OR`.

```php
Query::where('name', Operator::EQ, 'Chris')
    ->orWhere('name', Operator::EQ, 'Bob')
    ->compile();
// "name = 'Chris' OR name = 'Bob'"
```

### `whereNot(field, value)` / `orWhereNot(field, value)`

Negates a field equality check.

```php
Query::whereNot('name', 'Chris')->compile();
// "NOT name = 'Chris'"

Query::where('verified', Operator::EQ, true)
    ->orWhereNot('name', 'Chris')
    ->compile();
// "verified = true OR NOT name = 'Chris'"
```

### `whereIn(field, values)` / `orWhereIn(field, values)`

Matches any value in the given array.

```php
Query::whereIn('role', ['admin', 'editor'])->compile();
// "role IN ['admin', 'editor']"

Query::where('verified', Operator::EQ, true)
    ->orWhereIn('role', ['admin', 'editor'])
    ->compile();
// "verified = true OR role IN ['admin', 'editor']"
```

### `whereNotIn(field, values)` / `orWhereNotIn(field, values)`

Excludes any value in the given array.

```php
Query::whereNotIn('status', ['banned', 'suspended'])->compile();
// "status NOT IN ['banned', 'suspended']"
```

### `whereBetween(field, from, to)` / `orWhereBetween(field, from, to)`

Range filter using Meilisearch's `TO` operator.

```php
Query::whereBetween('price', 10, 100)->compile();
// "price 10 TO 100"
```

### `whereExists(field)` / `orWhereExists(field)`

Matches documents where the field exists.

```php
Query::whereExists('verified_at')->compile();
// "verified_at EXISTS"
```

### `whereIsNull(field)` / `orWhereIsNull(field)`

Matches documents where the field is `null`.

```php
Query::whereIsNull('deleted_at')->compile();
// "deleted_at IS NULL"
```

### `whereIsEmpty(field)` / `orWhereIsEmpty(field)`

Matches documents where the field is empty.

```php
Query::whereIsEmpty('tags')->compile();
// "tags IS EMPTY"
```

### `whereRaw(query)` / `orWhereRaw(query)`

Passes a raw filter string through the compiler unchanged. Useful for filter expressions the builder does not yet support natively.

```php
Query::whereRaw("name = 'Chris' OR name = 'Bob'")->compile();
// "name = 'Chris' OR name = 'Bob'"

Query::whereRaw("name = 'Chris'")
    ->orWhereRaw("name = 'Bob'")
    ->compile();
// "name = 'Chris' OR name = 'Bob'"
```

### Geo filters

#### `whereGeoRadius(lat, lng, distanceInMeters)` / `orWhereGeoRadius`

```php
Query::where('active', Operator::EQ, true)
    ->whereGeoRadius(48.8566, 2.3522, 1000)
    ->compile();
// "active = true AND _geoRadius(48.8566, 2.3522, 1000)"
```

#### `whereGeoBoundingBox(lat1, lng1, lat2, lng2)` / `orWhereGeoBoundingBox`

```php
Query::where('active', Operator::EQ, true)
    ->whereGeoBoundingBox(48.8566, 2.3522, 48.9, 2.4)
    ->compile();
// "active = true AND _geoBoundingBox([48.8566, 2.3522], [48.9, 2.4])"
```

---

## Nested / Grouped Queries

Pass a closure to `where()` or `orWhere()` to create a parenthesised group:

```php
Query::where(fn ($q) => $q
    ->where('name', Operator::EQ, 'Chris')
    ->orWhere('name', Operator::EQ, 'Bob')
)
->where('verified', Operator::EQ, true)
->compile();
// "(name = 'Chris' OR name = 'Bob') AND verified = true"
```

Groups can be nested to any depth:

```php
Query::where(fn ($q) => $q
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
// "(count >= 10 AND count <= 100 OR (name = 'Chris' OR name IS EMPTY OR email IS NULL)) OR name = 'Bob'"
```

---

## Supported Operators

See the [Meilisearch filter expression reference](https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#filter-operators) for full documentation on each operator.

| Enum case | Meilisearch syntax |
|---|---|
| `Operator::EQ` | `=` |
| `Operator::NEQ` | `!=` |
| `Operator::GT` | `>` |
| `Operator::GTE` | `>=` |
| `Operator::LT` | `<` |
| `Operator::LTE` | `<=` |
| `Operator::IN` | `IN` |
| `Operator::NOT` | `NOT` |
| `Operator::BETWEEN` | `TO` |
| `Operator::EXISTS` | `EXISTS` |
| `Operator::NULL` | `IS NULL` |
| `Operator::EMPTY` | `IS EMPTY` |

---

## Advanced: Using `ScoutAdapter` Directly

`forModel()` is a convenience wrapper around `ScoutAdapter`. If you need more control — for example, to swap in a custom compiler — you can instantiate `ScoutAdapter` directly:

```php
use Chr15k\MeilisearchAdvancedQuery\Adapters\ScoutAdapter;
use Chr15k\MeilisearchAdvancedQuery\Facades\Query;
use App\Models\Product;

$adapter = ScoutAdapter::for(
    Product::class,
    Query::where('status', Operator::EQ, 'active'),
);

$results = $adapter->search('leather', sort: ['price:asc']);
```

`ScoutAdapter` validates at instantiation that the given class exists, is an Eloquent model, and uses the `Searchable` trait — throwing `InvalidArgumentException` if any check fails.

---

## Debugging

Call `compile()` at any point in the chain to get the raw filter string without executing a search:

```php
Query::where(fn ($q) => $q
    ->whereIn('role', ['admin', 'editor'])
    ->orWhereIsEmpty('verified_at')
)
->orWhere('email', Operator::EQ, 'chris@example.com')
->compile();

// "(role IN ['admin', 'editor'] OR verified_at IS EMPTY) OR email = 'chris@example.com'"
```

---

## Architecture

The package is structured around four concerns:

- **Nodes** — immutable, typed value objects representing a single filter clause (`ComparisonNode`, `InNode`, `NotInNode`, `BetweenNode`, `GroupNode`, `RawNode`)
- **Compiler** — walks the node tree and produces a Meilisearch filter string (`MeilisearchCompiler`)
- **Query builder** — fluent API that constructs the node tree (`MeilisearchAdvancedQuery`)
- **Scout adapter** — bridges the compiled filter string to a Scout `Builder` (`ScoutAdapter`)

The `Compiler` and `Query` contracts are independently extensible — you can implement your own compiler (for a different search engine's filter syntax) or your own query builder without touching the rest of the package.

---

## Running Tests

```bash
composer test
```

Individual checks:

```bash
composer test:types   # PHPStan static analysis
composer test:lint    # Laravel Pint
composer test:unit    # Pest (with coverage)
```

---

## License

MIT — see [LICENSE](LICENSE) for details.