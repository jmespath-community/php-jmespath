<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class IndexExpression implements NodeInterface
{
    public function __construct(public NodeInterface $left, public NodeInterface $right)
    {
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

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $left = $this->left->evaluate($context);
        if ($this->isTruthy($left)) {
            $context->pushCurrent($left);
            $result = $this->right->evaluate($context);
            $context->popCurrent();
            return $result;
        }
        return null;
    }

    public function toArray(): array
    {
        return [
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'IndexExpression',
        ];
    }
}
