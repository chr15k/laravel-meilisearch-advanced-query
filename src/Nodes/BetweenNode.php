<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Nodes;

use Chr15k\MeilisearchAdvancedQuery\Contracts\Node;
use Chr15k\MeilisearchAdvancedQuery\Enums\BooleanOperator;

// node representation of: whereBetween('count', 1, 10)
final readonly class BetweenNode implements Node
{
    public function __construct(
        public string $field,
        public string|int|float|bool|null $from,
        public string|int|float|bool|null $to,
        public BooleanOperator $boolean
    ) {}
}
