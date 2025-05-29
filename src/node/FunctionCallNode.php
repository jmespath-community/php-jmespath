<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class FunctionCallNode implements NodeInterface
{
    /**
     * @param string $name
     * @param list<NodeInterface> $args
     */
    public function __construct(private string $name, private array $args)
    {
    }

    /**
     * @return array<mixed>|string|float|bool|int|null
     */
    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        $operands = array_map(fn (NodeInterface $node) => $node->evaluate($context), $this->args);
        return match ($this->name) {
            "zip" => $this->zip(...$operands),
            "group_by" => $this->groupBy(...$operands),
            'sum' => $this->sum(...$operands),
            "type" => $this->type($operands[0]),
            "keys" => array_keys($operands[0]),
            "join" => implode($operands[0], $operands[1]),
            "values" => array_values($operands[0]),
            "to_string" => is_string($operands[0]) ? $operands[0] : json_encode($operands[0]),
            "to_number" => is_numeric($operands[0]) ? floatval($operands[0]) : null,
            "not_null" => $this->not_null(...$operands),
            "to_array" => is_array($operands[0]) && array_is_list($operands[0]) ? $operands[0] : [$operands[0]],
            'length' => $this->length(...$operands),
            "reverse" => $this->reverse(...$operands),
            "abs" => abs($operands[0]),
            "min" => min(...$operands),
            "max" => max(...$operands),
            "max_by" => $this->max_by(...$operands),
            "map" => $this->map(...$operands),
            "min_by" => $this->min_by(...$operands),
            "merge" => array_merge(...$operands),
            "contains" => is_string($operands[0]) ? str_contains($operands[0], $operands[1]) : in_array($operands[1], $operands[0]),
            "starts_with" => str_starts_with($operands[0], $operands[1]),
            "ends_with" => str_ends_with($operands[0], $operands[1]),
            "ceil" => ceil($operands[0]),
            "find_first" => $this->find_first(...$operands),
            "lower" => strtolower($operands[0]),
            "upper" => strtoupper($operands[0]),
            "replace" => $this->replace(...$operands),
            "trim" => $this->trim($operands[0], $operands[1] ?? null),
            "trim_left" => ltrim($operands[0], characters: $operands[1] ?? " \n\r\t\v\0"),
            "trim_right" => rtrim($operands[0], characters: $operands[1] ?? " \n\r\t\v\0"),
            "pad_left" => str_pad($operands[0], $operands[1], $operands[2] ?? " ", STR_PAD_LEFT),
            "pad_right" => str_pad($operands[0], $operands[1], $operands[2] ?? " ", STR_PAD_RIGHT),
            "split" => $this->split($operands[0], $operands[1], $operands[2] ?? null),
            "find_last" => $this->find_last(...$operands),
            "items" => $this->items($operands[0]),
            "from_items" => $this->from_items($operands[0]),
            "floor" => intval(floor($operands[0])),
            "avg" => $this->avg($operands[0]),
            "sort" => $this->sort(...$operands),
            "sort_by" => $this->sort_by(...$operands),
            default => throw new \RuntimeException("Function {$this->name} not supported"),
        };
    }

    /**
     * @param list<mixed> $items
     * @return array<string, list<mixed>>
     */
    private function groupBy(array $items, \Closure $criteria): array
    {
        $result = [];
        foreach ($items as $item) {
            $group = $criteria($item);
            $result[$group] ??= [];
            $result[$group][] = $item;
        }
        return $result;
    }

    /**
     * @param list<int|float> $values
     * @return int|float
     */
    private function sum(array $values): int|float
    {
        $sum = 0;
        foreach ($values as $value) {
            $sum += $value;
        }
        return $sum;
    }

    /**
     * @return array{type: "Function", name: "string", children: list<array{type: string}>}
     */
    public function toArray(): array
    {
        return [
            'children' => array_map(fn (NodeInterface $node) => $node->toArray(), $this->args),
            'name' => $this->name,
            'type' => 'Function',

        ];
    }

    private function sort(float|bool|array|string|null $value): array|null
    {
        if (is_array($value)) {
            sort($value);
            return $value;
        }
        return null;
    }

    private function length(float|bool|array|string|null $value): int|null
    {
        if (is_array($value)) {
            return count($value);
        } elseif (is_string($value)) {
            return mb_strlen($value, 'UTF-8');
        }
        return null;
    }

    private function reverse(array|string|null $value): array|string|null
    {
        if (is_array($value)) {
            return array_reverse($value);
        }
        if (is_string($value)) {
            $array = preg_split('//u', $value);
            return implode('', array_reverse($array));
        }
        return null;
    }

    private function sort_by(array $elements, \Closure $closure): array
    {
        usort($elements, fn ($a, $b) => $closure($a) <=> $closure($b));
        return $elements;
    }

    private function items(array $items): array
    {
        $result = [];
        foreach ($items as $key => $value) {
            $result[] = [$key, $value];
        }
        return $result;
    }

    private function from_items(array $items): array
    {
        $result = [];
        foreach ($items as [$key, $value]) {
            $result[$key] = $value;
        }
        return $result;
    }

    private function not_null(mixed ...$operands): mixed
    {
        return array_find($operands, fn ($operand) => $operand !== null);
    }

    private function zip(array ...$operands): array
    {
        $i = 0;
        $result = [];
        while (true) {
            $row = [];
            foreach ($operands as $operand) {
                if (array_key_exists($i, $operand)) {
                    $row[] = $operand[$i];
                } else {
                    break 2;
                }
            }
            $result[] = $row;
            $i++;
        }
        return $result;
    }

    /**
     * @template T
     * @param list<T> $list
     * @return T
     */
    private function max_by(array $list, \Closure $expression): mixed
    {
        $maximumElement = array_pop($list);
        $maximumValue = $expression($maximumElement);
        foreach ($list as $item) {
            $newMaximumValue = $expression($item);
            if ($newMaximumValue > $maximumValue) {
                $maximumValue = $newMaximumValue;
                $maximumElement = $item;
            }
        }
        return $maximumElement;
    }

    /**
     * @template T
     * @param list<T> $list
     * @return T
     */
    private function min_by(array $list, \Closure $expression): mixed
    {
        $minimumElement = array_pop($list);
        $minimumValue = $expression($minimumElement);
        foreach ($list as $item) {
            $newMinimumValue = $expression($item);
            if ($newMinimumValue < $minimumValue) {
                $minimumValue = $newMinimumValue;
                $minimumElement = $item;
            }
        }
        return $minimumElement;
    }

    private function map(\Closure $expression, mixed $list): mixed
    {
        if (!is_array($list) || !array_is_list($list)) {
            return null;
        }
        return array_map($expression, $list);
    }

    private function find_first(string $subject, string $search, int|null $start = null, int|null $stop = null): int|null
    {
        $start ??= 0;
        if ($start < -1 * mb_strlen($search, 'utf-8')) {
            $start = 0;
        }
        $result = mb_strpos($subject, $search, $start, 'utf-8');
        if (!is_int($result) || $result > ($stop ?? PHP_INT_MAX)) {
            return null;
        }
        return $result;
    }

    private function find_last(string $subject, string $search, int|null $start = null, int|null $stop = null): int|null
    {
        $start ??= 0;
        if ($start < -1 * mb_strlen($search, 'utf-8')) {
            $start = 0;
        }
        $result = mb_strrpos($subject, $search, $start, 'utf-8');
        if (!is_int($result) || $result > ($stop ?? PHP_INT_MAX)) {
            return null;
        }
        return $result;
    }

    private function trim(string $subject, string|null $characters = null): string
    {
        if (empty($characters)) {
            return mb_trim($subject);
        } else {
            return mb_trim($subject, $characters);
        }
    }

    /**
     * @return list<string>
     */
    private function split(string $subject, string $search, int|null $count = null): array
    {
        if ($search === '') {
            $parts = str_split($subject);
            $result = array_splice($parts, 0, $count);
            if ($parts !== []) {
                $result[] = implode('', $parts);
            }
            return $result;
        }
        return explode($search, $subject, $count > 0 ? $count + 1 : ($count ?? PHP_INT_MAX));
    }

    /**
     * @return "array"|"object"|"string"|"number"|"boolean"|"null"
     */
    private function type(bool|int|float|string|array|null $value): string
    {
        return match (true) {
            is_array($value) => array_is_list($value) ? "array" : "object",
            is_string($value) => "string",
            is_int($value), is_float($value) => "number",
            is_bool($value) => "boolean",
            default => "null",
        };
    }

    /**
     * @param non-empty-list<int|float> $operands
     * @return float|int
     */
    private function avg(array $operands)
    {
        return array_sum($operands) / count($operands);
    }

    private function replace(string $subject, string $old, string $new, int|null $limit = null): string
    {
        if ($limit === 0) {
            return $subject;
        }
        $regex = "/" . preg_quote($old) . "/";
        return preg_replace($regex, $new, $subject, $limit ?? -1);
    }
}
