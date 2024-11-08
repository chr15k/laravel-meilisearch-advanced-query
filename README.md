# Laravel Meilisearch Advanced Query

[![Latest Stable Version](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/v)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![Total Downloads](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/downloads)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![Latest Unstable Version](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/v/unstable)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![License](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/license)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query) [![PHP Version Require](https://poser.pugx.org/chr15k/laravel-meilisearch-advanced-query/require/php)](https://packagist.org/packages/chr15k/laravel-meilisearch-advanced-query)

I wrote this package to help with generating custom Meilisearch queries using a query builder (instead of passing raw Meilisearch queries into Scout's search callback), see the following doc for context: [Customizing Search Engine](https://laravel.com/docs/11.x/scout#customizing-engine-searches) then check out Usage section below :)

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

User::search($term, function (Indexes $meilisearch, string $query, array $options) {

    $options['filter'] = "(name = 'Chris' OR name = 'Bob') AND verified = 'true'";
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

```php
public function where(string|Closure $column, mixed $operator = '=', mixed $value = null, string $boolean = 'AND');
public function orWhere(string|Closure $column, ?string $operator = null, mixed $value = null);

public function whereIn(string|Closure $column, mixed $value = null);
public function orWhereIn(string|Closure $column, mixed $value = null);

public function whereNotIn(string|Closure $column, mixed $value = null);
public function orWhereNotIn(string|Closure $column, mixed $value = null);

public function whereNot(string|Closure $column, mixed $value = null);
public function orWhereNot(string|Closure $column, mixed $value = null);

public function whereExists(string|Closure $column);
public function orWhereExists(string|Closure $column);

public function whereIsNull(string|Closure $column);
public function orWhereIsNull(string|Closure $column);

public function whereIsEmpty(string|Closure $column);
public function orWhereIsEmpty(string|Closure $column);

public function whereTo(string|Closure $column, mixed $from, mixed $to);
public function orWhereTo(string|Closure $column, mixed $from, mixed $to);
```

Examples:

```php
FilterBuilder::whereNot('name', 'Chris')->compile(); // => "NOT name 'Chris'"
FilterBuilder::whereTo('count', 1, 10)->compile(); // => "count 1 TO 10"
FilterBuilder::whereIsEmpty('name')->compile(); // => "name IS EMPTY"

FilterBuilder::whereNot('name', 'Chris')
    ->orWhereIsEmpty('name')
    ->compile(); // => "NOT name 'Chris' OR name IS EMPTY"

// nested
FilterBuilder::where(fn ($query) => $query
    ->whereNot('name', 'Chris')
    ->orWhereIsEmpty('name')
)
->orWhere('email', 'chris@example.com')
->compile(); // => "(NOT name 'Chris' OR name IS EMPTY) OR email = 'chris@example.com'"
```

### Supported search engine operators

Docs: [Meilisearch operators](https://www.meilisearch.com/docs/learn/filtering_and_sorting/filter_expression_reference#filter-operators)

```
'=', '!=', 'IN', 'NOT IN', '>=', '<=', '>', '<', 'TO', 'NOT', 'EXISTS', 'IS EMPTY', 'IS NULL'
```

Any of these operators can be called on `where()` or `orWhere()` methods as follows:

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
