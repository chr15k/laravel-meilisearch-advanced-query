<?php

declare(strict_types=1);

namespace Chr15k\MeilisearchAdvancedQuery\Enums;

enum Operator
{
    case EQ;
    case NEQ;
    case GTE;
    case LTE;
    case GT;
    case LT;

    case EXISTS;
    case IN;
    case EMPTY;
    case NULL;

    case NOT;
    case BETWEEN;

    public function toMeilisearch(): string
    {
        return match ($this) {
            self::EQ  => '=',
            self::NEQ => '!=',
            self::GTE => '>=',
            self::LTE => '<=',
            self::GT  => '>',
            self::LT  => '<',

            self::EXISTS => 'EXISTS',
            self::IN     => 'IN',
            self::EMPTY  => 'IS EMPTY',
            self::NULL   => 'IS NULL',

            self::NOT     => 'NOT',
            self::BETWEEN => 'TO',
        };
    }
}
