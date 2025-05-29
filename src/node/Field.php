<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Field implements NodeInterface
{
    public function __construct(public string $value)
    {
    }


    /**
     * @param Context $context
     * @return array<mixed>|string|float|bool|int|null
     */
    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $base = $context->current();
        return is_array($base) ? $base[$this->value] ?? null : null;
    }

    public function __toString()
    {
        return "\${$this->value}";
    }

    /**
     * @return array{type: "Field", name: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->value,
            'type' => "Field",
        ];
    }
}
