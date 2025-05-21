<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery;

use Chr15k\MeilisearchAdvancedQuery\Contracts\QuerySegment;

final class RawExpression implements QuerySegment
{
    public function __construct(
        public string $rawQuery,
        public string $boolean = 'AND',
        public bool $init = false
    ) {}

    public function compile(): string
    {
        return trim(sprintf('%s %s', $this->init ? '' : $this->boolean, $this->rawQuery));
    }
}
