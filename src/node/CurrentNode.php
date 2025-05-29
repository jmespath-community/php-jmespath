<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class CurrentNode implements NodeInterface
{
    public function getChildren(): array
    {
        return [];
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        return $context->current();
    }
    public function __toString()
    {
        return '@';
    }

    public function toArray(): array
    {
        return [
            'type' => 'Current'
        ];
    }
}
