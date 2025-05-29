<?php

namespace JmesPath;

/**
 * Tokenizes JMESPath expressions
 */
final readonly class ModernLexer
{
    /**
     * @param string $input
     * @throws \JsonException
     */
    public function tokenize(string $input): TokenStream
    {
        $buffer = new Reader($input);

        $result = [];

        while (null !== $token = $this->nextToken($buffer)) {
            $result[] = $token;
        }
        $result[] = new Token(TokenType::Eof, position: strlen($input));
        return new TokenStream($result);
    }
    private function nextToken(Reader $buffer): Token|null
    {
        $buffer->skipWhitespace();


        $start = $buffer->getPosition();
        $nextCharacter = $buffer->peek();
        $state = State::fromChar($nextCharacter);

        return match ($state) {
            State::Eof => null,
            State::UnquotedIdentifier => new Token(TokenType::UnquotedIdentifier, position: $start, value: $buffer->consumePattern('/[A-Za-z_][A-Za-z0-9_]*/')),
            State::Number => new Token(TokenType::Number, position: $start, value: (int) $buffer->consumePattern('/-?\d*/')),
            State::BasicToken => new Token(TokenType::fromSingleChar($nextCharacter), position: $start, value: $buffer->consume()),
            State::RawString => $this->consumeRawStringLiteral($buffer),
            State::JsonLiteral => $this->consumeJsonLiteral($buffer),
            State::QuotedIdentifier => new Token(TokenType::QuotedIdentifier, position: $start, value: $buffer->consumeQuotedIdentifier()),
            State::LBracket => $this->consumeLBracket($buffer),
            State::DollarSign => $this->consumeDollarSign($buffer),
            State::OperatorStart => $this->consumeOperator($buffer),
            State::Dash => $this->consumeDash($buffer),
        };
    }


    /**
     * @param Reader $buffer
     * @param array<string, TokenType> $map
     * @param TokenType $else
     * @return Token
     */
    private function map(Reader $buffer, array $map, TokenType $else): Token
    {
        $start = $buffer->getPosition();
        $value = $buffer->consume();
        $next = $buffer->peek();
        if (isset($map[$next])) {
            $type = $map[$next];
            $value .= $buffer->consume();
            return new Token(type: $type, position: $start, value: $value);
        }
        return new Token(type: $else, position: $start, value: $value);
    }

    private function consumeLBracket(Reader $buffer): Token
    {
        return $this->map($buffer, [
            ']' => TokenType::Flatten,
            '?' => TokenType::Filter,
        ], TokenType::Lbracket);
    }

    private function consumeOperator(Reader $buffer): Token
    {
        $char = $buffer->peek();
        return match ($char) {
            '!' => $this->map($buffer, map: ['=' => TokenType::NE], else: TokenType::Not),
            '<' => $this->map($buffer, map: ['=' => TokenType::LTE], else: TokenType::LT),
            '>' => $this->map($buffer, map: ['=' => TokenType::GTE], else: TokenType::GT),
            '=' => $this->map($buffer, map: ['=' => TokenType::EQ], else: TokenType::Assign),
            '&' => $this->map($buffer, map: ['&' => TokenType::And], else: TokenType::Expref),
            '|' => $this->map($buffer, map: ['|' => TokenType::Or], else: TokenType::Pipe),
            '/' => $this->map($buffer, map: ['/' => TokenType::Div], else: TokenType::Divide),
            default => throw new \RuntimeException("Invalid operator: {$char}"),
        };
    }

    private function consumeDollarSign(Reader $buffer): Token
    {
        $start = $buffer->getPosition();
        $buffer->consume();
        if (ctype_alpha($buffer->peek())) {
            return new Token(TokenType::Variable, position: $start, value: $buffer->consumePattern('/[A-Za-z_][A-Za-z0-9_]*/'));
        }
        return new Token(TokenType::Root, position: $start, value: '$');
    }

    private function consumeJsonLiteral(Reader $buffer): Token
    {
        $start = $buffer->getPosition();
        // Raw value
        $value = $buffer->consumeBetween();
        //        var_dump($value);die();
        if (preg_match('/^\d\.0+$/', $value)) {
            return new Token(TokenType::Literal, position: $start, value: intval($value));
        }
        return new Token(TokenType::Literal, position: $start, value: json_decode($value, true, flags: JSON_THROW_ON_ERROR));
    }

    private function consumeDash(Reader $buffer): Token
    {
        $start = $buffer->getPosition();
        $buffer->consume();
        if (ctype_digit($buffer->peek())) {
            return new Token(TokenType::Number, position: $start, value: -1 * (int) $buffer->consumePattern('/\d*/'));
        }
        return new Token(TokenType::Minus, position: $start, value: "-");
    }

    private function consumeRawStringLiteral(Reader $buffer): Token
    {
        $start = $buffer->getPosition();
        $raw = $buffer->consumeBetween();
        return new Token(TokenType::Literal, position: $start, value: strtr($raw, [
            '\\\\' => '\\',
            "\\'" => "'"
        ]));
    }
}
