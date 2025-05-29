<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class NotExpression implements NodeInterface
{
    public function __construct(public NodeInterface $expression)
    {
    }
    public function evaluate(Context $context): bool
    {
        return !$this->isTruthy($this->expression->evaluate($context));
    }

    private function isTruthy(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        if ($value === false) {
            return false;
        }
        if ($value === "") {
            return false;
        }
        if (is_array($value) && count($value) === 0) {
            return false;
        }
        return true;
    }

    public function __toString()
    {
        return "Inverse of {$this->expression}";
    }

    /**
     * @return array{type: "NotExpression", child: array{type: string}}
     */
    public function toArray(): array
    {
        return [
            'child' => $this->expression->toArray(),
            'type' => 'NotExpression',

        ];
    }
}
