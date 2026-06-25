<?php

namespace Chr15k\MeilisearchAdvancedQuery\Compilers;

use Chr15k\MeilisearchAdvancedQuery\Contracts\Compiler;
use Chr15k\MeilisearchAdvancedQuery\Contracts\Node;
use Chr15k\MeilisearchAdvancedQuery\Enums\BooleanOperator;
use Chr15k\MeilisearchAdvancedQuery\Enums\Operator;
use Chr15k\MeilisearchAdvancedQuery\Exceptions\UnsupportedNodeTypeException;
use Chr15k\MeilisearchAdvancedQuery\Nodes\BetweenNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\ComparisonNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\GroupNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\InNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\NotInNode;
use Chr15k\MeilisearchAdvancedQuery\Nodes\RawNode;

final class MeilisearchCompiler implements Compiler
{
    public function compile(Node $node, bool $isFirst = false): string
    {
        return match (true) {
            $node instanceof ComparisonNode => $this->comparison($node, $isFirst),
            $node instanceof InNode         => $this->in($node, $isFirst),
            $node instanceof NotInNode      => $this->notIn($node, $isFirst),
            $node instanceof GroupNode      => $this->group($node, $isFirst),
            $node instanceof RawNode        => $this->raw($node, $isFirst),
            $node instanceof BetweenNode    => $this->between($node, $isFirst),
            default                         => throw UnsupportedNodeTypeException::create($node::class),
        };
    }

    /**
     * @param  list<Node>  $nodes
     */
    public function compileAll(array $nodes): string
    {
        return collect($nodes)
            ->map(fn (Node $node, int $index): string => $this->compile($node, isFirst: $index === 0))
            ->filter()
            ->implode(' ');
    }

    private function in(InNode $node, bool $isFirst = false): string
    {
        return $this->prefix(
            sprintf('%s IN [%s]', $node->field, $this->escapeArray($node->values)),
            $node->boolean,
            $isFirst
        );
    }

    private function notIn(NotInNode $node, bool $isFirst = false): string
    {
        return $this->prefix(
            sprintf(
                '%s %s %s [%s]',
                $node->field,
                Operator::NOT->toMeilisearch(),
                Operator::IN->toMeilisearch(),
                $this->escapeArray($node->values)),
            $node->boolean,
            $isFirst
        );
    }

    private function between(BetweenNode $node, bool $isFirst = false): string
    {
        return $this->prefix(
            sprintf(
                '%s %s %s %s',
                $node->field,
                $node->from,
                Operator::BETWEEN->toMeilisearch(),
                $node->to
            ),
            $node->boolean,
            $isFirst
        );
    }

    private function raw(RawNode $node, bool $isFirst = false): string
    {
        return $this->prefix($node->query, $node->boolean, $isFirst);
    }

    private function comparison(ComparisonNode $node, bool $isFirst = false): string
    {
        $op = $node->operator->toMeilisearch();

        $expr = match ($node->operator) {
            Operator::EXISTS,
            Operator::NULL,
            Operator::EMPTY => sprintf('%s %s', $node->field, $op),
            Operator::NOT   => sprintf('%s %s = %s', $op, $node->field, $this->escape($node->value)),
            default         => sprintf('%s %s %s', $node->field, $op, $this->escape($node->value)),
        };

        return $this->prefix($expr, $node->boolean, $isFirst);
    }

    private function group(GroupNode $node, bool $isFirst = false): string
    {
        $inner = collect($node->children)
            ->map(fn (Node $child, int $index): string => $this->compile($child, isFirst: $index === 0))
            ->filter()
            ->implode(' ');

        return $this->prefix(sprintf('(%s)', $inner), $node->boolean, $isFirst);
    }

    private function escape(string|int|float|bool|null $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value),
            is_float($value) => (string) $value,
            is_null($value)  => 'NULL',
            default          => sprintf("'%s'", $value),
        };
    }

    /**
     * @param  list<string>  $values
     */
    private function escapeArray(array $values): string
    {
        return implode(', ', array_map($this->escape(...), $values));
    }

    private function prefix(string $expr, BooleanOperator $boolean, bool $isFirst): string
    {
        return $isFirst ? $expr : $boolean->value.' '.$expr;
    }
}
