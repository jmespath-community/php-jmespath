<?php

declare(strict_types=1);

namespace JmesPathCommunity;

class Reader
{
    private int $position = 0;
    private readonly int $length;

    private readonly array $buffer;

    public function __construct(
        string $buffer
    ) {
        $this->buffer = preg_split(pattern: '//u', subject: $buffer, flags: PREG_SPLIT_NO_EMPTY);
        $this->length = count($this->buffer);
    }

    public function peek(): null|string
    {
        return $this->buffer[$this->position] ?? null;
    }

    public function skipWhitespace(): void
    {
        $this->consumePattern('/^\s+/');
    }

    public function consumePattern(string $pattern): string|null
    {
        if ($this->position > $this->length) {
            throw new \RuntimeException('Overflow');
        }
        if (preg_match($pattern, implode(array_slice($this->buffer, $this->position)), $matches)) {
            $this->position += strlen($matches[0]);
            return $matches[0];
        }
        return null;
    }

    public function consume(): string
    {
        if ($this->position > $this->length) {
            throw new \RuntimeException('Overflow');
        }
        $result = $this->buffer[$this->position];
        $this->position++;

        return $result;
    }

    public function eof(): bool
    {
        return $this->position >= $this->length;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function consumeQuotedIdentifier(): string
    {
        $quoteChar = $this->consume();
        $value = '';
        while (!$this->eof()) {
            $character = $this->consume();
            // closing quote ends the string
            if ($character === $quoteChar) {
                return json_decode('"' . $value . '"', flags: JSON_THROW_ON_ERROR);
            }

            // handle backslash escapes
            if ($character === '\\' && $this->peek() === $quoteChar) {
                $this->consume();
                $value .= '\\';
                $value .= $quoteChar;
            } elseif ($character === '\\' && $this->peek() === '\\') {
                $this->consume();
                $value .= '\\';
                $value .= '\\';
            } else {
                $value .= $character;
            }
        }
        throw new UnterminatedStringException("Unterminated string starting at position {$this->position}");
    }

    public function consumeBetween(): string
    {
        $quoteChar = $this->consume();
        $value = '';
        while (!$this->eof()) {
            $character = $this->consume();
            // closing quote ends the string
            if ($character === $quoteChar) {
                return $value;
            }

            // handle backslash escapes
            if ($character === '\\' && $this->peek() === $quoteChar) {
                $this->consume();
                $value .= $quoteChar;
            } elseif ($character === '\\' && $this->peek() === '\\') {
                $this->consume();
                $value .= '\\';
                $value .= '\\';
            } else {
                $value .= $character;
            }
        }
        throw new UnterminatedStringException("Unterminated string starting at position {$this->position}");
    }
}
