<?php

declare(strict_types=1);

namespace JmesPathCommunity;

enum UnaryOperator
{
    case Flatten;

    case Minus;
    case Plus;

    public function label(): string
    {
        return match ($this) {
            self::Flatten => 'Flatten',
            self::Minus => 'Unary',
            self::Plus => 'Unary',
        };
    }
}
