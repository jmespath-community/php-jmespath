<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class MultiselectList implements NodeInterface
{
    /**
     * @param NodeInterface[] $children
     */
    public function __construct(private array $children)
    {
    }

    /**
     * @return list<mixed>
     */
    public function evaluate(Context $context): array
    {
        $result = [];
        foreach ($this->children as $child) {
            $resultItem = $child->evaluate($context);
            $result[] = $resultItem;
        }
        return $result;
    }

    public function __toString()
    {
        return "List: " . implode(', ', array_map(fn (NodeInterface $node) => $node, $this->children));
    }

    /**
     * @return array{type: "MultiSelectList", children: list<array{type: string}>}
     */
    public function toArray(): array
    {
        return [
            'children' => array_map(fn (NodeInterface $node) => $node->toArray(), $this->children),
            'type' => "MultiSelectList",

        ];
    }
}
