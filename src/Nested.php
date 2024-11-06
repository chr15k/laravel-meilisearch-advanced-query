<?php

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Contracts\FilterSegment;

class Nested implements FilterSegment
{
    public function __construct(
        /** @var Expression[] */
        public array $expressions = []
    ) {}

    public function compile(): string
    {
        return sprintf('(%s)', collect($this->expressions)->map->compile()->implode(' '));
    }
}
