<?php

declare(strict_types=1);

use Chr15k\MeilisearchAdvancedQuery\Compilers\MeilisearchCompiler;
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

it('compiles equality comparison', function (): void {
    $compiler = new MeilisearchCompiler;

    $node = new ComparisonNode(
        field: 'name',
        operator: Operator::EQ,
        value: 'Chris',
        boolean: BooleanOperator::And,
    );

    expect($compiler->compile($node))
        ->toBe("AND name = 'Chris'");
});

it('compiles greater than comparison', function (): void {
    $compiler = new MeilisearchCompiler;

    $node = new ComparisonNode(
        field: 'age',
        operator: Operator::GT,
        value: 18,
        boolean: BooleanOperator::And,
    );

    expect($compiler->compile($node))
        ->toBe('AND age > 18');

    expect($compiler->compile(node: $node, isFirst: true))
        ->toBe('age > 18');
});

it('prefixes non-first nodes with boolean operator', function (): void {
    $compiler = new MeilisearchCompiler;

    $result = $compiler->compileAll([
        new ComparisonNode(
            field: 'name',
            operator: Operator::EQ,
            value: 'Chris',
            boolean: BooleanOperator::And,
        ),
        new ComparisonNode(
            field: 'age',
            operator: Operator::GT,
            value: 18,
            boolean: BooleanOperator::And,
        ),
    ]);

    expect($result)
        ->toBe("name = 'Chris' AND age > 18");
});

it('compiles IN node', function (): void {
    $compiler = new MeilisearchCompiler;

    $node = new InNode(
        field: 'role',
        values: ['admin', 'editor'],
        boolean: BooleanOperator::And,
    );

    expect($compiler->compile(node: $node, isFirst: true))
        ->toBe("role IN ['admin', 'editor']");
});

it('compiles NOT IN node', function (): void {
    $compiler = new MeilisearchCompiler;

    $node = new NotInNode(
        field: 'role',
        values: ['guest'],
        boolean: BooleanOperator::And,
    );

    expect($compiler->compile(node: $node, isFirst: true))
        ->toBe("role NOT IN ['guest']");
});

it('compiles BETWEEN node', function (): void {
    $compiler = new MeilisearchCompiler;

    $node = new BetweenNode(
        field: 'price',
        from: 10,
        to: 50,
        boolean: BooleanOperator::And,
    );

    expect($compiler->compile(node: $node, isFirst: true))
        ->toBe('price 10 TO 50');
});

it('compiles raw node', function (): void {
    $compiler = new MeilisearchCompiler;

    $node = new RawNode(
        query: 'status = active',
        boolean: BooleanOperator::And,
    );

    expect($compiler->compile(node: $node, isFirst: true))
        ->toBe('status = active');
});

it('compiles grouped OR expression', function (): void {
    $compiler = new MeilisearchCompiler;

    $node = new GroupNode(
        children: [
            new ComparisonNode(
                field: 'role',
                operator: Operator::EQ,
                value: 'admin',
                boolean: BooleanOperator::Or,
            ),
            new ComparisonNode(
                field: 'role',
                operator: Operator::EQ,
                value: 'editor',
                boolean: BooleanOperator::Or,
            ),
        ],
        boolean: BooleanOperator::And,
    );

    expect($compiler->compile(node: $node, isFirst: true))
        ->toBe("(role = 'admin' OR role = 'editor')");
});

it('compiles nested groups correctly', function (): void {
    $compiler = new MeilisearchCompiler;

    $node = new GroupNode(
        children: [
            new GroupNode(
                children: [
                    new ComparisonNode(
                        field: 'active',
                        operator: Operator::EQ,
                        value: true,
                        boolean: BooleanOperator::And,
                    ),
                ],
                boolean: BooleanOperator::And,
            ),
        ],
        boolean: BooleanOperator::And,
    );

    expect($compiler->compile(node: $node, isFirst: true))
        ->toBe('((active = true))');
});

it('maintains correct ordering in compileAll', function (): void {
    $compiler = new MeilisearchCompiler;

    $result = $compiler->compileAll([
        new ComparisonNode(
            field: 'a',
            operator: Operator::EQ,
            value: 1,
            boolean: BooleanOperator::And,
        ),
        new ComparisonNode(
            field: 'b',
            operator: Operator::EQ,
            value: 2,
            boolean: BooleanOperator::And,
        ),
        new ComparisonNode(
            field: 'c',
            operator: Operator::EQ,
            value: 3,
            boolean: BooleanOperator::And,
        ),
    ]);

    expect($result)
        ->toBe('a = 1 AND b = 2 AND c = 3');
});

it('throws UnsupportedNodeTypeException for unsupported node types', function (): void {
    $unsupportedNode = new class implements Node {};

    (new MeilisearchCompiler)->compile($unsupportedNode);
})->throws(UnsupportedNodeTypeException::class);
