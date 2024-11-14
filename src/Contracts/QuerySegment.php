<?php

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

interface QuerySegment
{
    public function compile(): string;
}
