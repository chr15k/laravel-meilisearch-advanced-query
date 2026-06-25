<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Adapters;

use Chr15k\MeilisearchAdvancedQuery\Contracts\Query;
use Chr15k\MeilisearchAdvancedQuery\Contracts\SearchableModel;
use Chr15k\MeilisearchAdvancedQuery\Contracts\SearchAdapter;
use Closure;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

final readonly class ScoutAdapter implements SearchAdapter
{
    /**
     * @param  SearchableModel&Model  $model
     */
    public function __construct(
        private Model $model,
        private Query $query,
    ) {}

    public static function for(string $modelClass, Query $query): self
    {
        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException(sprintf('The class %s does not exist.', $modelClass));
        }

        $model = new $modelClass;

        if (! $model instanceof Model) {
            throw new InvalidArgumentException(sprintf('The class %s must be an Eloquent model.', $modelClass));
        }

        if (! in_array(Searchable::class, class_uses_recursive($model))) {
            throw new InvalidArgumentException(sprintf('The class %s must use the Searchable trait.', $modelClass));
        }

        /** @var SearchableModel&Model $model */
        return new self($model, $query);
    }

    /**
     * @param  list<string>  $sort
     * @return Builder<Model>
     */
    public function search(string $term = '', array $sort = []): Builder
    {
        $filter = $this->query->compile();

        /** @var Builder<Model> $builder */
        $builder = $this->model::search($term, $this->callback($filter, $sort));

        return $builder;
    }

    /**
     * @param  list<string>  $sort
     * @return Closure(object, string, array<string, mixed>): mixed
     */
    public function callback(string $filter, array $sort): Closure
    {
        // The $engine parameter is intentionally untyped. Typehinting a specific
        // engine (e.g. Meilisearch\Endpoints\Indexes) would couple this adapter
        // to a concrete Scout engine implementation. Scout passes the underlying
        // engine instance at runtime, so we suppress the PHPStan error here as
        // an explicit tradeoff in favour of engine agnosticism.
        return function ($engine, string $query, array $options) use ($filter, $sort) {
            $options['filter'] = $filter;
            $options['sort'] = $sort;

            /** @phpstan-ignore method.nonObject */
            return $engine->search($query, $options);
        };
    }
}
