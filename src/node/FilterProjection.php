<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class FilterProjection implements NodeInterface
{
    public function __construct(private NodeInterface $left, private NodeInterface $right, private NodeInterface $condition)
    {
    }

    private function isTruthy(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        if ($value === false) {
            return false;
        }
        if ($value === "") {
            return false;
        }
        if (is_array($value) && count($value) === 0) {
            return false;
        }
        return true;
    }

    public function evaluate(Context $context): array|null
    {
        $base = $this->left->evaluate($context);
        if (!is_array($base)) {
            return null;
        }

        $result = [];
        foreach ($base as $value) {
            $context->pushCurrent($value);
            $condition = $this->condition->evaluate($context);
            if (!$this->isTruthy($condition)) {
                continue;
            }
            $resultItem = $this->right->evaluate($context);
            $context->popCurrent();
            if (isset($resultItem)) {
                $result[] = $resultItem;
            }
        }
        return $result;
    }

    public function __toString()
    {
        return "FilterProjection({$this->left}, {$this->right})";
    }

    public function toArray(): array
    {
        return [
            'condition' => $this->condition->toArray(),
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'FilterProjection',
        ];
    }
}
