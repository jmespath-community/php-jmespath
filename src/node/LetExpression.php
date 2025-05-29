<?php

declare(strict_types=1);
namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class LetExpression implements NodeInterface
{
    /**
     * @param list<Binding> $bindings
     */
    public function __construct(private NodeInterface $expression, private array $bindings)
    {
    }


    public function __toString(): string
    {
        return "let some bindings in {$this->expression}";
    }

    /**
     * @return array<mixed>|string|float|bool|int|null
     */
    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $scoped = [];
        foreach ($this->bindings as $binding) {
            $scoped[$binding->name()] = $binding->evaluate($context);
        }

        $context->pushScope($scoped);
        $result = $this->expression->evaluate($context);
        $context->popScope();
        return $result;
    }

    /**
     * @return array{expression: array{type: string}, type: "LetExpression", bindings: list<array{type: string}>}
     */
    public function toArray(): array
    {
        return [
            'bindings' => array_map(fn (NodeInterface $node) => $node->toArray(), $this->bindings),
            'expression' => $this->expression->toArray(),
            'type' => 'LetExpression',
        ];
    }
}
