<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class NumberNode implements NodeInterface
{
    public function __construct(public int $value)
    {
    }

    public function evaluate(Context $context): int
    {
        return $this->value;
    }

    public function __toString()
    {
        return "Number: $this->value";
    }

    /**
     * @return array{type: "Index", value: int}
     */
    public function toArray(): array
    {
        return ['type' => 'Index', 'value' => $this->value];
    }
}
