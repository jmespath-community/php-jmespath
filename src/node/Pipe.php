<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class Pipe implements NodeInterface
{
    public function __construct(public NodeInterface $left, public NodeInterface $right)
    {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $left = $this->left->evaluate($context);
        $context->pushCurrent($left);
        $right = $this->right->evaluate($context);
        $context->popCurrent();
        return $right;
    }
    public function __toString()
    {
        return "Pipe({$this->left}, {$this->right})";
    }

    public function toArray(): array
    {
        return [
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'Pipe',

        ];
    }
}
