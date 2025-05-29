<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class LiteralNode implements NodeInterface
{
    /**
     * @param string|int|array<mixed>|float|bool|null $value
     */
    public function __construct(public string|int|array|float|bool|null $value)
    {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        return $this->value;
    }

    public function __toString()
    {
        return "Literal: " . json_encode($this->value, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{type: "Literal", value: mixed}
     */
    public function toArray(): array
    {
        return [
            'type' => 'Literal',
            'value' => $this->value,
        ];
    }
}
