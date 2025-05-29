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

    public function evaluate(Context $context): \Closure
    {
        return function (mixed $value) {
            $context = new Context($value);
            return $this->expression->evaluate($context);
        };
    }

    public function __toString()
    {
        return "Expression reference: {$this->expression}";
    }

    public function toArray(): array
    {
        return [
            'child' => $this->expression->toArray(),
            'type' => "ExpressionReference",

        ];
    }
}
