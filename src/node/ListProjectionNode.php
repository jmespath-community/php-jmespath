<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class ListProjectionNode implements NodeInterface
{
    public function __construct(public NodeInterface $left, public NodeInterface $right = new CurrentNode())
    {
    }

    /**
     * @return list<mixed>
     */
    public function evaluate(Context $context): array
    {
        $base = $this->left->evaluate($context);
        $result = [];
        foreach ($base as $value) {
            $context->pushCurrent($value);
            $resultItem = $this->right->evaluate($context);
            $context->popCurrent();
            if (isset($resultItem)) {
                $result[] = $resultItem;
            }
        }
        return $result;
    }

    public function __toString()
    {
        return "LP({$this->left}, {$this->right})";
    }

    /**
     * @return array{left: array{type: string}, right: array{type: string}, type: "Projection"}
     */
    public function toArray(): array
    {
        return [
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'Projection',

        ];
    }
}
