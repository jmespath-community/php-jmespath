<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;
use JmesPathCommunity\UnaryOperator;

final readonly class UnaryExpression implements NodeInterface
{
    public function __construct(private NodeInterface $child, private UnaryOperator $operator)
    {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $operand = $this->child->evaluate($context);
        return match ($this->operator) {
            UnaryOperator::Flatten => $this->flatten($operand),
            UnaryOperator::Minus => -1 * $operand,
            UnaryOperator::Plus => 1 * $operand,
        };
    }

    private function flatten(null|array $value): array|null
    {
        if (!is_array($value)) {
            return null;
        }
        $result = [];
        foreach ($value as $sub) {
            if (is_array($sub) && array_is_list($sub)) {
                $result = [...$result, ...$sub];
            } else {
                $result[] = $sub;
            }
        }
        return $result;
    }

    public function __toString()
    {
        return "{$this->operator->name}({$this->child})";
    }

    public function toArray(): array
    {
        if ($this->operator === UnaryOperator::Flatten) {
            return [
                'child' => $this->child->toArray(),
                'type' => $this->operator->label(),
            ];
        }
        return [
            'operand' => $this->child->toArray(),
            'operator' => $this->operator->name,
            'type' => $this->operator->label(),


        ];
    }
}
