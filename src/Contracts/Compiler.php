<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Contracts;

interface Compiler
{
    public function compile(Node $node, bool $isFirst = false): string;

    /** @param list<Node> $nodes */
    public function compileAll(iterable $nodes): string;
}
