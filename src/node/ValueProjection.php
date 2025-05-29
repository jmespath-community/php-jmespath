<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class ValueProjection implements NodeInterface
{
    public function __construct(public NodeInterface $left, public NodeInterface $right)
    {
    }

    public function evaluate(Context $context): array|null
    {
        $base = $this->left->evaluate($context);

        if (!is_array($base) || empty($base)) {
            return null;
        }
        $result = [];
        foreach ($base as $value) {
            $context->pushCurrent($value);
            $resultItem = $this->right->evaluate($context);
            $context->popCurrent();
            if (isset($resultItem)) {
                $result[] = $resultItem;
            }
        }
        return $result;
    }
    public function toArray(): array
    {
        return [
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'ValueProjection',

        ];
    }
}
