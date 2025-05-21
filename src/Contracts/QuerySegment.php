<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

interface QuerySegment
{
    public function compile(): string;
}
