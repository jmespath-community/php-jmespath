<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\ArithmethicType;
use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Arithmetic implements NodeInterface
{
    public function __construct(private NodeInterface $left, private ArithmethicType $operator, private NodeInterface $right)
    {
    }

    public function evaluate(Context $context): float|int
    {
        $left = $this->left->evaluate($context);
        $right = $this->right->evaluate($context);
        if (!(is_int($left) || is_float($left)) || !(is_int($right) || is_float($right))) {
            throw new \RuntimeException('Arithmetic operators only work on numbers');
        }
        return match ($this->operator) {
            ArithmethicType::Modulo => $left % $right,
            ArithmethicType::Div => floor($left / $right),
            ArithmethicType::Divide => $left / $right,
            ArithmethicType::Minus => $left - $right,
            ArithmethicType::Plus => $left + $right,
            ArithmethicType::Multiply => $left * $right,
            ArithmethicType::Star => $left * $right,
        };
    }

    public function toArray(): array
    {
        return [
            'operator' => $this->operator->name,
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'Arithmetic',
        ];
    }
}
