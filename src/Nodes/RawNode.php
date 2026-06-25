<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Nodes;

use Chr15k\MeilisearchAdvancedQuery\Contracts\Node;
use Chr15k\MeilisearchAdvancedQuery\Enums\BooleanOperator;

final readonly class RawNode implements Node
{
    public function __construct(
        public string $query,
        public BooleanOperator $boolean
    ) {}
}
