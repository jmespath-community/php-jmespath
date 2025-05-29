<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class Identity implements NodeInterface
{
    public function __toString(): string
    {
        return "Identity";
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        return $context->current();
    }

    public function toArray(): array
    {
        return [
            'type' => 'Identity'
        ];
    }
}
