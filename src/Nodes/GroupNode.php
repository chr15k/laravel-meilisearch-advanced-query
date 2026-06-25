<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Nodes;

use Chr15k\MeilisearchAdvancedQuery\Contracts\Node;
use Chr15k\MeilisearchAdvancedQuery\Enums\BooleanOperator;

// node representation of: (Q1 AND Q2) OR (Q3 OR Q4)
final readonly class GroupNode implements Node
{
    /**
     * @param  list<Node>  $children
     */
    public function __construct(
        public array $children,
        public BooleanOperator $boolean
    ) {}
}
