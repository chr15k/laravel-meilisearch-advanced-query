<?php

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Contracts\QuerySegment;

class NestedExpression implements QuerySegment
{
    public function __construct(
        /** @var Expression[] */
        public array $expressions = [],
        public string $boolean = 'AND',
        public bool $init = false
    ) {}

    public function compile(): string
    {
        return trim(sprintf(
            '%s (%s)',
            $this->init ? '' : $this->boolean,
            collect($this->expressions)->map->compile()->implode(' ')
        ));
    }
}
