<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

interface CompilesFilter
{
    /** @return list<Node> */
    public function nodes(): array;
}
