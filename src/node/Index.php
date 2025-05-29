<?php

declare(strict_types=1);

namespace JmesPath\node;

use JmesPath\Context;
use JmesPath\NodeInterface;

final readonly class Index implements NodeInterface
{
    public function __construct(private int $index)
    {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $arrayValue = $context->current();
        if (is_array($arrayValue)) {
            if ($this->index < 0) {
                return $arrayValue[count($arrayValue) + $this->index] ?? null;
            }
            return $arrayValue[$this->index] ?? null;
        }
        return null;
    }

    public function __toString()
    {
        return "Index({$this->index})";
    }

    public function toArray(): array
    {
        return [
            'type' => 'Index',
            'value' => $this->index,
        ];
    }
}
