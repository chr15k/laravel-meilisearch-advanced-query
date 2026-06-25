<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Enums;

enum BooleanOperator: string
{
    case And = 'AND';
    case Or = 'OR';
}
