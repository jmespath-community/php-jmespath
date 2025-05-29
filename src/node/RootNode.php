<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class RootNode implements NodeInterface
{
    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        return $context->root();
    }
    public function __toString()
    {
        return '$';
    }

    public function toArray(): array
    {
        return [
            'type' => 'Root'
        ];
    }
}
