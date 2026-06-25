<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Nodes;

use Chr15k\MeilisearchAdvancedQuery\Contracts\Node;
use Chr15k\MeilisearchAdvancedQuery\Enums\BooleanOperator;

// node representation of: whereNotIn('role', ['admin', 'editor'])
final readonly class NotInNode implements Node
{
    /**
     * @param  list<string>  $values
     */
    public function __construct(
        public string $field,
        public array $values,
        public BooleanOperator $boolean
    ) {}
}
