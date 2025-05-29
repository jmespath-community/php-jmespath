<?php

declare(strict_types=1);

namespace JmesPath;

enum TokenType
{
    case Eof;
    case Variable;
    case Assign;
    case UnquotedIdentifier;
    case QuotedIdentifier;
    case Rbracket;
    case Rparen;
    case Comma;
    case Colon;
    case Rbrace;
    case Number;
    case Current;
    case Root;
    case Expref;
    case Pipe;
    case Or;
    case And;
    case EQ;
    case GT;
    case LT;
    case GTE;
    case LTE;
    case NE;
    case Plus;
    case Minus;
    case Multiply;
    case Divide;
    case Modulo;
    case Div;
    case Flatten;
    case Star;
    case Filter;
    case Dot;
    case Not;
    case Lbrace;
    case Lbracket;
    case Lparen;
    case Literal;

    public static function fromSingleChar(string $char): self|null
    {
        // See https://github.com/jmespath-community/typescript-jmespath/blob/main/src/Lexer.ts#L6
        return match ($char) {
            "(" => self::Lparen,
            ")" => self::Rparen,
            "*" => self::Star,
            "," => self::Comma,
            "." => self::Dot,
            ":" => self::Colon,
            "@" => self::Current,
            "]" => self::Rbracket,
            "{" => self::Lbrace,
            "}" => self::Rbrace,
            "+" => self::Plus,
            "%" => self::Modulo,
            // Special unicode chars
            "−" => self::Minus,
            "×" => self::Multiply,
            "÷" => self::Divide,
            default => null
        };
    }

    public function bindingPower(): int
    {
        return match ($this) {
            self::Lparen => 60,
            self::Lbracket => 55,
            self::Lbrace => 50,
            self::Not => 45,
            self::Dot => 40,
            self::Filter => 21,
            self::Star => 20,
            self::Flatten => 9,
            self::Multiply, self::Modulo, self::Divide, self::Div => 7,
            self::Plus, self::Minus => 6,
            self::NE, self::LTE, self::GTE, self::LT, self::GT, self::EQ => 5,
            self::And => 3,
            self::Or => 2,
            self::Pipe, self::Assign => 1,
            default => 0
        };
    }

    public function hasPrecedenceOver(self $other): bool
    {
        return $this->bindingPower() > $other->bindingPower();
    }
}
