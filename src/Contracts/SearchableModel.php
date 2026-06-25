<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

/**
 * Represents an Eloquent model that uses the Laravel Scout Searchable trait.
 *
 * This interface is not intended to be implemented directly. It exists solely
 * as a PHPStan type contract, allowing static analysis to understand that a
 * given model has the full API surface of both Model and Searchable mixed in.
 *
 * At runtime, conformance is validated via class_uses_recursive() in ScoutAdapter::for().
 *
 * @mixin Model
 */
interface SearchableModel
{
    /** @return Builder<Model> */
    public static function search(string $query = '', mixed $callback = null): Builder;
}
