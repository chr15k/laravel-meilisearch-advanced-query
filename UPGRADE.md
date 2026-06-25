# Upgrade Guide

## Upgrading from v2 to v3

v3 is a full architectural rewrite. The public API has changed — this guide covers everything you need to update.

---

### Requirements

| | v2 | v3 |
|---|---|---|
| PHP | 8.1+ | **8.3+** |
| Laravel | 11–12 | **12–13** |
| Laravel Scout | 10+ | **11+** |
| Meilisearch PHP SDK | 1.11+ | **1.16+** |

---

### Entry point

The builder is no longer instantiated via a static `::for(Model::class)` call that binds a model upfront. In v3, building a query and executing a Scout search are separate concerns.

**Before:**
```php
MeilisearchAdvancedQuery::for(User::class)
    ->where('name', 'Chris')
    ->search($term);
```

**After:**
```php
use Chr15k\MeilisearchAdvancedQuery\Facades\Query;
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;

Query::where('name', Operator::EQ, 'Chris')
    ->forModel(User::class)
    ->search($term);
```

Or without the facade:

```php
use Chr15k\MeilisearchAdvancedQuery\MeilisearchAdvancedQuery;

app(MeilisearchAdvancedQuery::class)
    ->where('name', Operator::EQ, 'Chris')
    ->forModel(User::class)
    ->search($term);
```

---

### Operators are now an enum

String operators are gone. Every operator is now a case on the `Operator` enum. This affects `where()` and `orWhere()`.

**Before:**
```php
->where('count', '>=', 10)
->where('name', '!=', 'Chris')
->where('status', '=', 'active')
```

**After:**
```php
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;

->where('count', Operator::GTE, 10)
->where('name', Operator::NEQ, 'Chris')
->where('status', Operator::EQ, 'active')
```

The full operator mapping:

| Old string | New enum case |
|---|---|
| `=` | `Operator::EQ` |
| `!=` | `Operator::NEQ` |
| `>` | `Operator::GT` |
| `>=` | `Operator::GTE` |
| `<` | `Operator::LT` |
| `<=` | `Operator::LTE` |
| `NOT` | `Operator::NOT` |
| `IN` | `Operator::IN` |
| `EXISTS` | `Operator::EXISTS` |
| `IS NULL` | `Operator::NULL` |
| `IS EMPTY` | `Operator::EMPTY` |
| `TO` | `Operator::BETWEEN` |

---

### `whereTo()` renamed to `whereBetween()`

The method name has been updated to better reflect what it does. The signature is also flatter — `from` and `to` are separate arguments rather than an array.

**Before:**
```php
->whereTo('count', 1, 10)
->orWhereTo('count', 1, 10)
```

**After:**
```php
->whereBetween('count', 1, 10)
->orWhereBetween('count', 1, 10)
```

---

### `sort()` removed from the query builder

Sorting is now passed directly to `search()` on the `ScoutAdapter`.

**Before:**
```php
MeilisearchAdvancedQuery::for(User::class)
    ->where('name', 'Chris')
    ->sort(['name:desc', 'email:asc'])
    ->search($term);
```

**After:**
```php
Query::where('name', Operator::EQ, 'Chris')
    ->forModel(User::class)
    ->search($term, sort: ['name:desc', 'email:asc']);
```

---

### `inspect()` and `dump()` removed

These debug helpers have been removed. Use `compile()` to inspect the generated filter string:

```php
Query::where('name', Operator::EQ, 'Chris')->compile();
// "name = 'Chris'"
```

---

### Scout is now opt-in

The query builder no longer has any dependency on Laravel Scout. Compiling a filter string works without Scout installed. Scout is only involved when you call `forModel()` or use `ScoutAdapter` directly.

If you were previously using `MeilisearchAdvancedQuery` purely to generate filter strings and passing them elsewhere, you can now do so without the Scout or Meilisearch SDK overhead.

---

### `ScoutAdapter` for advanced use

If you need direct access to the Scout adapter — for example to swap in a custom compiler — `ScoutAdapter::for()` is available:

```php
use Chr15k\MeilisearchAdvancedQuery\Adapters\ScoutAdapter;

$adapter = ScoutAdapter::for(
    Product::class,
    Query::where('status', Operator::EQ, 'active'),
);

$results = $adapter->search('leather', sort: ['price:asc']);
```

---

### Facade

A `Query` facade is now registered automatically:

```php
use Chr15k\MeilisearchAdvancedQuery\Facades\Query;
```

This proxies to a fresh builder instance on each call — there is no shared state.