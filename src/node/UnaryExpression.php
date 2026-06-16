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

    public function evaluate(Context $context): array|null|float|int
    {
        $operand = $this->child->evaluate($context);
        return match ($this->operator) {
            UnaryOperator::Flatten => $this->flatten($operand),
            UnaryOperator::Minus => $this->minus($operand),
            UnaryOperator::Plus => $this->plus($operand),
        };
    }

    private function flatten(array|bool|float|int|string|null $value): array|null
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

    private function minus(float|int|bool|array|string|null $operand): int|float
    {
        if (is_int($operand) || is_float($operand)) {
            return -1 * $operand;
        }
        throw new \RuntimeException('not-a-number');
    }

    private function plus(float|int|bool|array|string|null $operand): int|float
    {
        if (is_int($operand) || is_float($operand)) {
            return 1 * $operand;
        }
        throw new \RuntimeException('not-a-number');
    }
}
