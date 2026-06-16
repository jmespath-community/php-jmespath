<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;
use JmesPathCommunity\Token;
use JmesPathCommunity\TokenType;

final readonly class Index implements NodeInterface
{
    public function __construct(private int $index)
    {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $arrayValue = $context->current();
        if (is_array($arrayValue)) {
            if ($this->index < 0) {
                return $arrayValue[count($arrayValue) + $this->index] ?? null;
            }
            return $arrayValue[$this->index] ?? null;
        }
        return null;
    }

    public function toArray(): array
    {
        return [
            'type' => 'Index',
            'value' => $this->index,
        ];
    }

    public static function fromToken(Token $token): Index
    {
        if ($token->type === TokenType::Number && is_int($token->value)) {
            return new Index($token->value);
        }
        throw new \InvalidArgumentException('Token type must be Number and its value must be integer');
    }
}
