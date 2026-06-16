<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Ternary implements NodeInterface
{
    public function __construct(
        private NodeInterface $condition,
        private NodeInterface $trueExpr,
        private NodeInterface $falseExpr,
    ) {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $condition = $this->condition->evaluate($context);
        if ($this->isTruthy($condition)) {
            return $this->trueExpr->evaluate($context);
        }
        return $this->falseExpr->evaluate($context);
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

    public function toArray(): array
    {
        return [
            'condition' => $this->condition->toArray(),
            'falseExpr' => $this->falseExpr->toArray(),
            'trueExpr' => $this->trueExpr->toArray(),
            'type' => 'Ternary',
        ];
    }
}
