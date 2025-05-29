<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class OrExpression implements NodeInterface
{
    public function __construct(public NodeInterface $left, public NodeInterface $right)
    {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $left = $this->left->evaluate($context);
        if (!$this->isTruthy($left)) {
            return $this->right->evaluate($context);
        }
        return $left;
    }
    public function __toString()
    {
        return "Or({$this->left}, {$this->right})";
    }

    /**
     * @return array{left: array{type: string}, right: array{type: string}, type: "OrExpression"}
     */
    public function toArray(): array
    {
        return [
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'OrExpression',

        ];
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
}
