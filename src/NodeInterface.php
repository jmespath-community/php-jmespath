<?php

declare(strict_types=1);

namespace JmesPath;

interface NodeInterface extends \Stringable
{
    public function evaluate(Context $context): array|string|null|float|bool|int|\Closure;

    public function toArray(): array;
}
