<?php

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

interface FilterSegment
{
    public function compile(): string;
}
