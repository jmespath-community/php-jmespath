<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Projection implements NodeInterface
{
    public function __construct(private NodeInterface $left, private NodeInterface $right)
    {
    }

    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $allowString = $this->left instanceof IndexExpression && $this->left->right instanceof Slice;

        $base = $this->left->evaluate($context);
        if (is_string($base) && $allowString) {
            $context->pushCurrent($base);
            $result = $this->right->evaluate($context);
            $context->popCurrent();
            return $result;
        }


        if ((!is_array($base)) || !array_is_list($base)) {
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
            'type' => 'Projection',
        ];
    }
}
