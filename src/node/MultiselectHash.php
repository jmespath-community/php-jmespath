<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class MultiselectHash implements NodeInterface
{
    /**
     * @param array<string, NodeInterface> $children
     */
    public function __construct(private array $children)
    {
    }

    public function evaluate(Context $context): array
    {
        return array_map(fn (NodeInterface $node) => $node->evaluate($context), $this->children);
    }

    public function __toString()
    {
        return ' TODO HASH NODE';
    }

    /**
     * @return array{children: list<array{value: mixed, type:"KeyValuePair", name: string}>}
     */
    public function toArray(): array
    {
        $children = [];
        foreach ($this->children as $key => $child) {
            $children[] = [
                'value' => $child->toArray(),
                'type' => 'KeyValuePair',
                'name' => $key,

            ];
        }
        return [
            'children' => $children,
            'type' => 'MultiSelectHash',

        ];
    }
}
