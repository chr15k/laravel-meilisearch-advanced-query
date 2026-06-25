# CONTRIBUTING

Contributions are welcome, and are accepted via pull requests.
Please review these guidelines before submitting any pull requests.

## Process

1. Fork the project
1. Create a new branch
1. Code, test, commit and push
1. Open a pull request detailing your changes. Make sure to follow the [template](.github/PULL_REQUEST_TEMPLATE.md)

## Guidelines

* Please ensure the coding style running `composer lint`.
* Send a coherent commit history, making sure each individual commit in your pull request is meaningful.
* You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
* Please remember that we follow [SemVer](http://semver.org/).

## Setup

Clone your fork, then install the dev dependencies. The test application skeleton is built automatically via `post-autoload-dump`, so no separate setup step is needed:
```bash
composer install
```

## Code Quality

Fix formatting and apply refactors:
```bash
composer fix
```

Check formatting without modifying files (used in CI):
```bash
composer test:lint
```

Check for available refactors without applying them:
```bash
composer test:refactor
```

Check types:
```bash
composer test:types
```

## Tests

Run unit tests:
```bash
composer test:unit
```

## CI

Run the full gate (lint, refactor, types, unit):
```bash
composer test
```