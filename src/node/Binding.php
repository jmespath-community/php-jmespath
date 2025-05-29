<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

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
    public function name(): string
    {
        return $this->variable->name;
    }
}
