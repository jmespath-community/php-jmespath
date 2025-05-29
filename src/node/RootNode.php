<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class RootNode implements NodeInterface
{
    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        return $context->root();
    }
    public function toArray(): array
    {
        return [
            'type' => 'Root'
        ];
    }
}
