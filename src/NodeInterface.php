<?php

declare(strict_types=1);

namespace JmesPathCommunity;

/**
 * @phpstan-type Json array<Json>|string|null|float|bool|int
 */
interface NodeInterface
{
    /**
     * @return Json
     */
    public function evaluate(Context $context): array|string|null|float|bool|int;

    /**
     * @return array{type: string}
     */
    public function toArray(): array;
}
