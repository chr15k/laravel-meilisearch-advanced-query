<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Nodes;

use Chr15k\MeilisearchAdvancedQuery\Contracts\Node;
use Chr15k\MeilisearchAdvancedQuery\Enums\BooleanOperator;
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;

// node representation of: where('status', '=', 'active')
final readonly class ComparisonNode implements Node
{
    public function __construct(
        public string $field,
        public Operator $operator,
        public string|int|float|bool|null $value,
        public BooleanOperator $boolean
    ) {}
}
