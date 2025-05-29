<?php

declare(strict_types=1);

namespace JmesPath;

enum ComparatorType
{
    case EQ;
    case GT;
    case LT;
    case GTE;
    case LTE;
    case NE;


    public static function fromTokenType(TokenType $tokenType): self
    {
        return match ($tokenType) {
            TokenType::EQ => self::EQ,
            TokenType::NE => self::NE,
            TokenType::GT => self::GT,
            TokenType::GTE => self::GTE,
            TokenType::LT => self::LT,
            TokenType::LTE => self::LTE,
            default => throw new \InvalidArgumentException("Token type is not a comparator: {$tokenType->name}")
        };
    }
}
