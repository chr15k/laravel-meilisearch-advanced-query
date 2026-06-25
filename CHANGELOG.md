# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 - 2026-06-25

### Added

- AST-based architecture: every filter clause is now represented as a typed, immutable node (`ComparisonNode`, `InNode`, `NotInNode`, `BetweenNode`, `GroupNode`, `RawNode`)
- `MeilisearchCompiler` — dedicated compiler that walks the node tree and produces a Meilisearch filter string; fully independent of Scout
- `Operator` enum — replaces raw string operators on `where()` and `orWhere()`
- `BooleanOperator` enum — replaces raw `AND`/`OR` strings throughout the builder
- `ScoutAdapter` — bridges the compiled filter string to a Scout `Builder`; validates that the given model exists, is Eloquent, and uses the `Searchable` trait
- `Query` facade — proxies to a fresh `MeilisearchAdvancedQuery` instance on each call
- `MeilisearchManager` — resolves fresh query builder instances from the container and forwards method calls
- `forModel(string $modelClass)` — fluent method on the query builder that hands off to `ScoutAdapter`
- `whereBetween()` / `orWhereBetween()` — replaces the removed `whereTo()` / `orWhereTo()`
- `whereNot()` / `orWhereNot()` — dedicated negation methods
- `orWhereNotIn()` — OR variant for `NOT IN` filter
- `UnsupportedNodeTypeException` — thrown by the compiler when an unrecognised node type is encountered
- `SearchableModel` contract — PHPStan-only interface used to express that a model uses the Scout `Searchable` trait, without requiring models to implement it explicitly
- `Query` contract — defines the full fluent builder API
- `Compiler` contract — defines the node compilation API
- `SearchAdapter` contract — defines the search execution API
- PHP 8.3 minimum requirement
- Laravel 12 / 13 support
- Laravel Scout 11+ support
- Meilisearch PHP SDK 1.16+ support
- String escaping in `escape()` now handles single quotes within values

### Changed

- `MeilisearchAdvancedQuery::for(Model::class)` removed — query building and Scout search are now separate concerns; use `Query::where(...)->forModel(Model::class)->search()` instead
- `where()` and `orWhere()` now require `Operator` enum cases instead of raw strings
- `whereTo()` renamed to `whereBetween()`; `orWhereTo()` renamed to `orWhereBetween()`
- `sort()` removed from the query builder — sorting is now passed as an argument to `ScoutAdapter::search()`
- `compile()` now always returns a `string` (previously could return `self`)
- Service provider now binds `Compiler::class` as a singleton, enabling the compiler to be swapped via the container

### Removed

- `inspect()` debug helper — use `compile()` instead
- `dump()` debug helper — use `compile()` instead
- `sort()` on the query builder — pass sort expressions to `search()` on `ScoutAdapter`
- Raw string operators on `where()` / `orWhere()` — replaced by `Operator` enum

---

## 2.2.0 - 2025-05-21

### Added

- Added new `whereGeo*` methods by [@giuseppecastaldo](https://github.com/giuseppecastaldo) in https://github.com/chr15k/laravel-meilisearch-advanced-query/pull/1
- Added PHPStan, Pint, Rector, Github workflow, and Laravel 12 support by [@chr15k](https://github.com/chr15k) in https://github.com/chr15k/laravel-meilisearch-advanced-query/pull/2

## 2.1.0 - 2024-11-15

### Added

- Add new raw query methods: `whereRaw()` & `orWhereRaw()`

## 2.0.3 - 2024-11-14

### Changed

- Readme updated

## 2.0.2 - 2024-11-14

### Removed

- Remove facade from composer.json

## 2.0.1 - 2024-11-14

### Removed

- Remove unused code

## 2.0.0 - 2024-11-14

### Changed

- Refactored package — now requires a searchable model class on builder creation
- No longer need to call Scout `search()` separately
- Readme updated

## 1.0.4 - 2024-11-13

### Changed

- Readme updated
- Small refactor

## 1.0.3 - 2024-11-09

### Changed

- Readme updated

## 1.0.2 - 2024-11-09

### Changed

- Readme updated

### Added

- Added Builder interface

## 1.0.1 - 2024-11-08

### Changed

- Readme updated

## 1.0.0 - 2024-11-08

### Added

- Initial release