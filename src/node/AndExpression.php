<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class AndExpression implements NodeInterface
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

    /**
     * @param Context $context
     * @return array|string|float|bool|int|null
     */
    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $left = $this->left->evaluate($context);
        if ($this->isTruthy($left)) {
            return $this->right->evaluate($context);
        }
        return $left;
    }

    public function __toString()
    {
        return "And({$this->left}, {$this->right})";
    }

    public function toArray(): array
    {
        return [
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'AndExpression',
        ];
    }
}
