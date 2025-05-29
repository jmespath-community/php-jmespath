<?php

declare(strict_types=1);

namespace JmesPathCommunity;

final class TokenStream
{
    private int $position = 0;

    /**
     * @param list<Token> $tokens
     */
    public function __construct(private readonly array $tokens)
    {
    }
    public function length(): int
    {
        return count($this->tokens);
    }

    public function current(): Token
    {
        if (!isset($this->tokens[$this->position])) {
            throw new \RuntimeException('Overflow');
        }
        return $this->tokens[$this->position];
    }

    public function advance(): Token
    {
        $result = $this->current();
        $this->position++;
        return $result;
    }

    public function match(TokenType ...$types): bool
    {
        if (in_array($this->current()->type, $types, true)) {
            $this->advance();
            return true;
        }
        return false;
    }

    public function expect(TokenType ...$types): Token
    {
        if (!in_array($this->current()->type, $types)) {
            $typeString = implode('|', array_map(fn (TokenType $type) => $type->name, $types));
            throw new \Exception("Expected {$typeString}, found {$this->current()->type->name} at {$this->position}");
        }
        return $this->advance();
    }

    public function dump(): string
    {
        $dump = [];
        foreach (array_slice($this->tokens, $this->position) as $token) {
            $dump[] = "{$token->type->name}({$token->type->bindingPower()})#{$token->position}: {$token->value}";
        }

        return implode(", ", $dump);
    }

    public function peek(TokenType ...$types): bool
    {
        return in_array($this->current()->type, $types, true);
    }

    public function matchChain(TokenType ...$types): bool
    {
        $startPosition = $this->position;
        foreach ($types as $type) {
            if (!$this->match($type)) {
                $this->position = $startPosition;
                return false;
            }
        }
        return true;
    }

    public function eof(): bool
    {
        return $this->position >= count($this->tokens);
    }

    public function toArray(): array
    {
        $tokens = $this->tokens;
        array_pop($tokens);
        return array_map(fn (Token $token) => $token->toArray(), $tokens);
    }
}
