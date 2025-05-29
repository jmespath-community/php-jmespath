<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class Variable implements NodeInterface
{
    public function __construct(public string $name)
    {
    }

    public function __toString(): string
    {
        return "Variable {$this->name}";
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        return $context->getValue($this);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => 'Variable',

        ];
    }
}
