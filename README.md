# Laravel Meilisearch Advanced Query

[![Latest Stable Version](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/v)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![Total Downloads](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/downloads)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![Latest Unstable Version](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/v/unstable)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![License](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/license)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![PHP Version Require](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/require/php)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query)

I wrote this package to help with generating more refined Meilisearch queries using an intuitive query builder (replacing the need to construct your own raw Meilisearh queries when working with Scout's advanced filter option), see the following doc for context: [Customizing Search Engine](https://laravel.com/docs/11.x/scout#customizing-engine-searches), then check out the Usage section below :)

This packages assumes you have installed and setup [Laravel Scout](https://laravel.com/docs/11.x/scout) with [Meilisearch driver](https://laravel.com/docs/11.x/scout#meilisearch)

###

## Install

```bash
composer require chr15k/laravel-meilisearch-advanced-query
```

## Usage

#### Before

```php
<?php
use App\Models\User;
use Meilisearch\Endpoints\Indexes;

// raw meilisearch query string
$filter = "(name = 'Chris' OR name = 'Bob') AND verified = 'true'";

User::search($term, function (Indexes $meilisearch, string $query, array $options) use ($filter) {

    $options['filter'] = $filter;
    $options['sort'] = "[name:desc]";

    return $meilisearch->search($query, $options);
})->paginate();
```

#### After

```php
<?php
use App\Models\User;
use Chr15k\MeilisearchAdvancedQuery\Facades\FilterBuilder;

$callback = FilterBuilder::where(fn ($query) => $query
    ->where('name', 'Chris')
    ->orWhere('name', 'Bob')
)
    ->where('verified', true)
    ->sort('name', 'desc')
    ->callback();

$builder = User::search($term, $callback);

// continue to chain Scout methods
$results = $builder->paginate();
```

### Raw query

If you just need the generated query from the builder then call `->compile()` instead of `->callback()`

## Builder Methods

#### # where(column, operator(optional), value(optional), boolean(optional))

```php
FilterBuilder::where('name', 'Chris')->compile(); // "name = 'Chris'"
```

#### # orWhere(column, operator(optional), value(optional))

```php
FilterBuilder::orWhere('name', 'Chris')->compile(); // "name = 'Chris'"

FilterBuilder::where('name', 'Bob')
    ->orWhere('name', 'Chris')
    ->compile(); // "name = 'Bob' OR name = 'Chris'"
```

#### # whereIn(column, value(optional))

```php
FilterBuilder::whereIn('name', ['Chris', 'Bob'])->compile(); // "name IN ['Chris','Bob']"
```

#### # orWhereIn(column, value(optional))

```php
FilterBuilder::orWhereIn('name', ['Chris', 'Bob'])->compile(); // "name IN ['Chris','Bob']"

FilterBuilder::where('email', 'chris@example.com')
    ->orWhereIn('name', ['Chris', 'Bob'])->compile(); // "email = 'chris@example.com' OR name IN ['Chris','Bob']"
```

#### # whereNotIn(column, value(optional))

```php
FilterBuilder::whereNotIn('name', ['Chris', 'Bob'])->compile(); // "name NOT IN ['Chris','Bob']"
```

#### # orWhereNotIn(column, value(optional))

```php
FilterBuilder::where('email', 'chris@example.com')
    ->orWhereNotIn('name', ['Chris', 'Bob'])->compile(); // "email = 'chris@example.com' OR name NOT IN ['Chris','Bob']"
```

#### # whereNot(column, value(optional))

```php
FilterBuilder::whereNot('name', 'Chris')->compile(); // => "NOT name 'Chris'"
```

#### # orWhereNot(column, value(optional))

```php
FilterBuilder::where('email', 'chris@example.com')
    ->orWhereNot('name', 'Chris')->compile(); // => "email = 'chris@example.com' OR NOT name 'Chris'"
```

#### # whereIsEmpty(column)

```php
FilterBuilder::whereIsEmpty('name')->compile(); // => "name IS EMPTY"
```

#### # orWhereIsEmpty(column)

```php
FilterBuilder::whereNot('name', 'Chris')
    ->orWhereIsEmpty('name')
    ->compile(); // => "NOT name 'Chris' OR name IS EMPTY"
```

#### # whereTo(column, from, to)

```php
FilterBuilder::whereTo('count', 1, 10)->compile(); // => "count 1 TO 10"
```

#### # orWhereTo(column, from, to)

```php
FilterBuilder::where('email', 'chris@example.com')
    ->orWhereTo('count', 1, 10)->compile(); // => "email = 'chris@example.com' OR count 1 TO 10"
```

#### # whereExists(column)

#### # orWhereExists(column)

#### # whereIsNull(column)

#### # orWhereIsNull(column)

### Nested / grouped queries

```php
FilterBuilder::where(fn ($query) => $query
    ->whereNot('name', 'Chris')
    ->orWhereIsEmpty('name')
)
->orWhere('email', 'chris@example.com')
->compile(); // => "(NOT name 'Chris' OR name IS EMPTY) OR email = 'chris@example.com'"
```

### Sorting

In addition to the above methods, you can also call sort on the builder instance as follows:

```php
FilterBuilder::where('name', 'Chris')->sort('name', 'desc')->compile();
```

### Supported search engine operators

Docs: [Meilisearch operators](https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#filter-operators)

```
'=', '!=', 'IN', 'NOT IN', '>=', '<=', '>', '<', 'TO', 'NOT', 'EXISTS', 'IS EMPTY', 'IS NULL'
```

Alternatively to the methods above, any of these operators can be called on `where()` or `orWhere()` methods, example:

```php
FilterBuilder::where('name', 'NOT', 'Chris')->compile(); // => "NOT name 'Chris'"
FilterBuilder::where('count', 'TO', [1, 10])->compile(); // => "count 1 TO 10"
FilterBuilder::where('name', 'IS EMPTY')->compile(); // => "name IS EMPTY"
```

Calling without operator will default to equals (same behaviour as Eloquent):

```php
FilterBuilder::where('name', 'Chris')->compile(); // => "name = 'Chris'"
```

## Tests

```bash
composer test
```
