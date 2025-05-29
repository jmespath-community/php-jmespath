<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Subexpression implements NodeInterface
{
    public function __construct(private NodeInterface $left, private NodeInterface $right)
    {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $left = $this->left->evaluate($context);
        if ($left !== null) {
            $context->pushCurrent($left);
            $result = $this->right->evaluate($context);
            $context->popCurrent();
            return $result;
        }
        return null;
    }

    public function __toString()
    {
        return "Subexpression({$this->left}, {$this->right})";
    }


    public function toArray(): array
    {
        return [
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'Subexpression',

        ];
    }
}
