<?php

declare(strict_types=1);

namespace JmesPath;

enum ArithmethicType
{
    case Plus;
    case Minus;
    case Multiply;
    case Divide;
    case Modulo;
    case Div;
    case Star;

    public static function fromTokenType(TokenType $tokenType): self
    {
        return match ($tokenType) {
            TokenType::Plus => self::Plus,
            TokenType::Minus => self::Minus,
            TokenType::Multiply => self::Multiply,
            TokenType::Divide => self::Divide,
            TokenType::Modulo => self::Modulo,
            TokenType::Div => self::Div,
            TokenType::Star => self::Star,
            default => throw new \InvalidArgumentException("Token type is not a comparator: {$tokenType->name}")
        };
    }
}
