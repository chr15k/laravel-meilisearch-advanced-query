<?php

namespace Chr15k\MeilisearchAdvancedQuery\Exceptions;

use InvalidArgumentException;

final class UnsupportedNodeTypeException extends InvalidArgumentException
{
    public static function create(string $nodeClass): static
    {
        return new self(sprintf('Unsupported node type: %s', $nodeClass));
    }
}
