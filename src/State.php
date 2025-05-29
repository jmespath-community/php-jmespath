<?php

declare(strict_types=1);

namespace JmesPath;

enum State
{
    case UnquotedIdentifier;
    case Number;
    case BasicToken;
    case DollarSign;
    case RawString;
    case QuotedIdentifier;
    case JsonLiteral;

    case Eof;
    case LBracket;
    case OperatorStart;

    case Dash;

    private static function isAlpha(string $char): bool
    {
        $ordinal = ord($char);
        return (
            ($ordinal >= 65 && $ordinal <= 90) // A-Z
            || ($ordinal >= 97 && $ordinal <= 122) // a-z
            || $ordinal === 95 // _
        );
    }

    public static function fromChar(null|string $char): self
    {
        if ($char === null) {
            return self::Eof;
        }

        if (self::isAlpha($char)) {
            return self::UnquotedIdentifier;
        }
        if (TokenType::fromSingleChar($char) !== null) {
            return self::BasicToken;
        }

        if (in_array($char, ['!', '<', '=', '>', '&', '|', '/'], true)) {
            return self::OperatorStart;
        }


        return match ($char) {
            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9" => self::Number,
            "$" => self::DollarSign,
            "-" => self::Dash,
            "`" => self::JsonLiteral,
            '"' => self::QuotedIdentifier,
            "'" => self::RawString,
            "[" => self::LBracket,
            default => die("Unexpected state token: $char\n"),
        };
    }
}
