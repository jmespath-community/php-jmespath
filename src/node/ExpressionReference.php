<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class ExpressionReference implements NodeInterface
{
    public function __construct(private NodeInterface $expression)
    {
    }

    public function evaluate(Context $context): never
    {
        throw new \RuntimeException('Cannot evaluate ExpressionReference directly');
    }

    public function getClosure(): \Closure
    {
        $expression = $this->expression;
        return static fn (array|string|null|float|bool $value) => $expression->evaluate(new Context($value));
    }

    public function toArray(): array
    {
        return [
            'child' => $this->expression->toArray(),
            'type' => "ExpressionReference",

        ];
    }
}
