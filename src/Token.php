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

    public function toArray(): array
    {
        return [
//            'start' => $this->position,
            'type' => $this->type->name,
            'value' => $this->value,
        ];
    }
}
