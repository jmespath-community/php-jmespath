<?php

declare(strict_types=1);

namespace JmesPathCommunity;

interface NodeInterface extends \Stringable
{
    public function evaluate(Context $context): array|string|null|float|bool|int|\Closure;

    public function toArray(): array;
}
