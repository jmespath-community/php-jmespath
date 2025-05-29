<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Identity implements NodeInterface
{
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
