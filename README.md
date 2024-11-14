# Laravel Meilisearch Advanced Query

[![Latest Stable Version](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/v)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![Total Downloads](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/downloads)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![Latest Unstable Version](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/v/unstable)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![License](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/license)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![PHP Version Require](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/require/php)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query)

I wrote this package to help with generating more refined Meilisearch queries using an intuitive query builder (replacing the need to construct your own raw Meilisearch queries when working with Scout's advanced filter option), check out the [Usage](https://github.com/chr15k/laravel-meilisearch-advanced-query?tab=readme-ov-file#usage) section below :)

---

**NOTE** - This packages assumes you have installed and setup the following:

-   [Laravel Scout](https://laravel.com/docs/11.x/scout)
-   [Meilisearch driver](https://laravel.com/docs/11.x/scout#meilisearch)

---

###

## Install

```bash
composer require chr15k/laravel-meilisearch-advanced-query
```

## Usage

[Go here](https://laravel.com/docs/11.x/scout#customizing-engine-searches) to see how custom search engine queries are used with Laravel Scout.

```php
<?php
use App\Models\User;
use Chr15k\MeilisearchAdvancedQuery\MeilisearchQuery;

$builder = MeilisearchQuery::for(User::class)
    ->where('name', 'Chris')
    ->whereIn('email', ['chris@example.com', 'bob@example.com'])
    ->orWhere(fn ($query) => $query
        ->whereTo('login_count', 50, 400)
        ->orWhereIsEmpty('verified_at')
    )->sort(['name:desc', 'email:asc'])
     ->search($term); // returns Scout Builder instance

// continue to chain Scout methods
$results = $builder->paginate();
```

> [!NOTE]
> The above example replaces the standard `User::search($term, $callback)` method

## Builder Methods

#### # where(column, operator(optional), value(optional), boolean(optional))

```php
MeilisearchQuery::for(User::class)->where('name', 'Chris');

// "name = 'Chris'"
```

#### # orWhere(column, operator(optional), value(optional))

```php
MeilisearchQuery::for(User::class)->orWhere('name', 'Chris')

// "name = 'Chris'"

MeilisearchQuery::for(User::class)
    ->where('name', 'Bob')
    ->orWhere('name', 'Chris')

// "name = 'Bob' OR name = 'Chris'"
```

#### # whereIn(column, value(optional))

```php
MeilisearchQuery::for(User::class)
    ->whereIn('name', ['Chris', 'Bob']);

// "name IN ['Chris','Bob']"
```

#### # orWhereIn(column, value(optional))

```php
MeilisearchQuery::for(User::class)
    ->orWhereIn('name', ['Chris', 'Bob']);

// "name IN ['Chris','Bob']"

MeilisearchQuery::for(User::class)
    ->where('email', 'chris@example.com')
    ->orWhereIn('name', ['Chris', 'Bob']) ;

// "email = 'chris@example.com' OR name IN ['Chris','Bob']"
```

#### # whereNotIn(column, value(optional))

```php
MeilisearchQuery::for(User::class)
    ->whereNotIn('name', ['Chris', 'Bob']);

// "name NOT IN ['Chris','Bob']"
```

#### # orWhereNotIn(column, value(optional))

```php
MeilisearchQuery::for(User::class)
    ->where('email', 'chris@example.com')
    ->orWhereNotIn('name', ['Chris', 'Bob']);

// "email = 'chris@example.com' OR name NOT IN ['Chris','Bob']"
```

#### # whereNot(column, value(optional))

```php
MeilisearchQuery::for(User::class)
    ->whereNot('name', 'Chris');

// "NOT name 'Chris'"
```

#### # orWhereNot(column, value(optional))

```php
MeilisearchQuery::for(User::class)
    ->where('email', 'chris@example.com')
    ->orWhereNot('name', 'Chris');

// "email = 'chris@example.com' OR NOT name 'Chris'"
```

#### # whereIsEmpty(column)

```php
MeilisearchQuery::for(User::class)->whereIsEmpty('name');

// "name IS EMPTY"
```

#### # orWhereIsEmpty(column)

```php
MeilisearchQuery::for(User::class)
    ->whereNot('name', 'Chris')
    ->orWhereIsEmpty('name');

// "NOT name 'Chris' OR name IS EMPTY"
```

#### # whereTo(column, from, to)

```php
MeilisearchQuery::for(User::class)->whereTo('count', 1, 10);

// "count 1 TO 10"
```

#### # orWhereTo(column, from, to)

```php
MeilisearchQuery::for(User::class)
    ->where('email', 'chris@example.com')
    ->orWhereTo('count', 1, 10);

// "email = 'chris@example.com' OR count 1 TO 10"
```

#### # whereExists(column)

#### # orWhereExists(column)

#### # whereIsNull(column)

#### # orWhereIsNull(column)

---

### Nested / grouped queries

```php
MeilisearchQuery::for(User::class)
    ->where(fn ($query) => $query
        ->whereNot('name', 'Chris')
        ->orWhereIsEmpty('name')
    )
    ->orWhere('email', 'chris@example.com');

// "(NOT name 'Chris' OR name IS EMPTY) OR email = 'chris@example.com'"
```

### Sorting

In addition to the above methods, you can also call sort on the builder instance as follows:

#### Single column sort:

```php
MeilisearchQuery::for(User::class)
    ->where('name', 'Chris')
    ->orWhere('name', 'Bob')
    ->sort('name:desc');
```

#### Multiple column sort:

```php
MeilisearchQuery::for(User::class)
    ->where('name', 'Chris')
    ->orWhere('name', 'Bob')
    ->sort(['name:desc', 'email:asc']);
```

For more information on sorting see this [link](https://www.meilisearch.com/docs/learn/filtering_and_sorting/sort_search_results)

### Supported search engine operators

Docs: [Meilisearch operators](https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#filter-operators)

```
'=', '!=', 'IN', 'NOT IN', '>=', '<=', '>', '<', 'TO', 'NOT', 'EXISTS', 'IS EMPTY', 'IS NULL'
```

Alternatively to the methods above, any of these operators can be called on `where()` or `orWhere()` methods, example:

```php
MeilisearchQuery::for(User::class)->where('name', 'NOT', 'Chris'); // "NOT name 'Chris'"
MeilisearchQuery::for(User::class)->where('count', 'TO', [1, 10]); // "count 1 TO 10"
MeilisearchQuery::for(User::class)->where('name', 'IS EMPTY'); // "name IS EMPTY"
```

Calling without operator will default to equals (same behaviour as Eloquent):

```php
MeilisearchQuery::for(User::class)->where('name', 'Chris'); // "name = 'Chris'"
```

## Debugging / helpers

To get the raw query string from the builder, call `compile()` instead of `search()`

```php
MeilisearchQuery::for(User::class)
    ->where(fn ($query) => $query
        ->whereIn('name', ['Chris', 'Bob'])
        ->orWhereIsEmpty('verified_at')
    )
    ->orWhere('email', 'chris@example.com')
    ->compile();

// "(name IN ['Chris','Bob'] OR verified_at IS EMPTY) OR email = 'chris@example.com'"
```

To inspect the current builder instance properties:

```php
MeilisearchQuery::for(User::class)->where('name', 'Chris')->inspect();
```

Or use the `dump` helper:

```php
MeilisearchQuery::for(User::class)->where('name', 'Chris')->dump();
```

## Tests

```bash
composer test
```
