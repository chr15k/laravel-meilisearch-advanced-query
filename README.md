# Laravel Meilisearch Advanced Query

Generate options using a convenient query builder to be used to perform advanced customization of search behaviour.

Context: https://laravel.com/docs/11.x/scout#customizing-engine-searches

This packages assumes you have installed and setup [Laravel Scout](https://laravel.com/docs/11.x/scout) with [Meilisearch driver](https://laravel.com/docs/11.x/scout#meilisearch)

###

## Install

```bash
composer require chr15k/laravel-meilisearch-advanced-query
```

## Usage

#### Before

```php
<?php
use App\Models\User;
use Meilisearch\Endpoints\Indexes;

User::search($term, function (Indexes $meilisearch, string $query, array $options) {

    $options['filter'] = "(name = 'Chris' OR name = 'Bob') AND verified = 'true'";
    $options['sort'] = "[name:desc]";

    return $meilisearch->search($query, $options);
});
```

#### After

```php
<?php
use App\Models\User;
use Chr15k\MeilisearchAdvancedQuery\Facades\FilterBuilder;

// use callbacks to nest queries (same as Eloquent)
$callback = FilterBuilder::where(fn ($query) => $query
    ->where('name', 'Chris')
    ->orWhere('name', 'Bob')
)->where('verified', true)
 ->sort('name', 'desc')
 ->callback();

User::search($term, $callback);
```

### Raw query

If you just need the generated query from the builder then you can call `->compile()` instead of `->callback()`

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
