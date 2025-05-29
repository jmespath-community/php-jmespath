<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\ComparatorType;
use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Comparator implements NodeInterface
{
    public function __construct(private NodeInterface $left, private ComparatorType $operator, private NodeInterface $right)
    {
    }


    private function jmespathEquals(mixed $a, mixed $b): bool
    {
        if (is_array($a) && is_array($b)) {
            if (array_is_list($a) && array_is_list($b)) {
                // Compare as lists: order and values must match
                if (count($a) !== count($b)) {
                    return false;
                }
                foreach ($a as $i => $value) {
                    if (!$this->jmespathEquals($value, $b[$i])) {
                        return false;
                    }
                }
                return true;
            } elseif (!array_is_list($a) && !array_is_list($b)) {
                // Compare as objects: same keys and equal values
                if (count($a) !== count($b)) {
                    return false;
                }
                foreach ($a as $key => $value) {
                    if (!array_key_exists($key, $b) || !$this->jmespathEquals($value, $b[$key])) {
                        return false;
                    }
                }
                return true;
            } else {
                // One is a list, the other is an object — not equal
                return false;
            }
        }

        // Compare numbers and strings normally
        if (is_numeric($a) && is_numeric($b)) {
            return $a + 0 === $b + 0;
        }

        // Strict comparison for other types
        return $a === $b;
    }

    public function evaluate(Context $context): bool
    {
        $left = $this->left->evaluate($context);
        $right = $this->right->evaluate($context);
        return match ($this->operator) {
            ComparatorType::LT => $left < $right,
            ComparatorType::LTE => $left <= $right,
            ComparatorType::GT => $left > $right,
            ComparatorType::GTE => $left >= $right,
            ComparatorType::EQ => $this->jmespathEquals($left, $right),
            ComparatorType::NE => !$this->jmespathEquals($left, $right),
        };
    }

    public function toArray(): array
    {
        return [
            'name' => $this->operator->name,
            'left' => $this->left->toArray(),
            'right' => $this->right->toArray(),
            'type' => 'Comparator',
        ];
    }
}
