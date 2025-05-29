<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class Binding implements NodeInterface
{
    public function __construct(private Variable $variable, private NodeInterface $reference)
    {
    }


    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        return $this->reference->evaluate($context);
    }

    public function toArray(): array
    {
        return [
            'type' => "Binding",
            'reference' => $this->reference->toArray(),
            'variable' => $this->variable->name,
        ];
    }

    public function __toString()
    {
        return "Binding({$this->variable} := {$this->reference})";
    }

    public function name(): string
    {
        return $this->variable->name;
    }
}
