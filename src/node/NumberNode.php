<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

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
