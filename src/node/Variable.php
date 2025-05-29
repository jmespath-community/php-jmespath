<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Variable implements NodeInterface
{
    public function __construct(public string $name)
    {
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
