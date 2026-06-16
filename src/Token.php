<?php

declare(strict_types=1);

namespace JmesPathCommunity;

final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public int $position,
        public mixed $value = null
    ) {
    }

    public function bindingPower(): int
    {
        return $this->type->bindingPower();
    }

    /**
     * @return array{type: string, value: mixed}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->name,
            'value' => $this->value,
        ];
    }
}
