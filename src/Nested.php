<?php

namespace Chr15k\MeilisearchFilter;

use Chr15k\MeilisearchFilter\Contracts\FilterSegment;

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
