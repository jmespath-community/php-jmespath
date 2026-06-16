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

    private function require(int $index): NodeInterface
    {
        if (!isset($this->args[$index])) {
            throw new \RuntimeException("Missing required argument $index");
        }
        return $this->args[$index];
    }
    private function arg(int $index): NodeInterface|null
    {
        return $this->args[$index] ?? null;
    }
    /**
     * @return array<mixed>|string|float|bool|int|null
     */
    public function evaluate(Context $context): array|string|null|float|bool|int
    {
        return match ($this->name) {
            "zip" => $this->zip($context),
            "group_by" => $this->group_by($context),
            'sum' => $this->sum($context),
            "type" => $this->type($context),
            "keys" => $this->keys($context),
            "join" => $this->join($context),
            "values" => $this->array_values($context),
            "to_string" => $this->to_string($context),
            "to_number" => $this->to_number($context),
            "not_null" => $this->not_null($context),
            "to_array" => $this->to_array($context),
            'length' => $this->length($context),
            "reverse" => $this->reverse($context),
            "abs" => $this->abs($context),
            "min" => $this->min($context),
            "max" => $this->max($context),
            "max_by" => $this->max_by($context),
            "map" => $this->map($context),
            "min_by" => $this->min_by($context),
            "merge" => $this->merge($context),
            "contains" => $this->contains($context),
            "starts_with" => $this->starts_with($context),
            "ends_with" => $this->ends_with($context),
            "ceil" => $this->ceil($context),
            "find_first" => $this->find_first($context),
            "lower" => $this->lower($context),
            "upper" => $this->upper($context),
            "replace" => $this->replace($context),
            "trim" => $this->trim($context),
            "trim_left" => $this->trim_left($context),
            "trim_right" => $this->trim_right($context),
            "pad_left" => $this->pad_left($context),
            "pad_right" => $this->pad_right($context),
            "split" => $this->split($context),
            "find_last" => $this->find_last($context),
            "items" => $this->items($context),
            "from_items" => $this->from_items($context),
            "floor" => $this->floor($context),
            "avg" => $this->avg($context),
            "sort" => $this->sort($context),
            "sort_by" => $this->sort_by($context),
            default => throw new \RuntimeException("Function {$this->name} not supported"),
        };
    }

    private function group_by(Context $context): array
    {
        $items = $this->arg(0)?->evaluate($context);
        if (!is_array($items)) {
            throw new \RuntimeException('First argument to groupBy must resolve to an array');
        }
        if (!$this->args[1] instanceof ExpressionReference) {
            throw new \RuntimeException('Second argument to groupBy must be an ExpressionReference');
        }
        $groupFunction = $this->args[1]->getClosure();
        $result = [];
        foreach ($items as $item) {
            $group = $groupFunction($item);
            $result[$group] ??= [];
            $result[$group][] = $item;
        }
        return $result;
    }

    /**
     * @return int|float
     */
    private function sum(Context $context): int|float
    {
        $sum = 0;
        if (count($this->args) > 1) {
            throw new \RuntimeException('Sum expects 1 array argument');
        }
        $numbers = $this->arg(0)?->evaluate($context) ?? [];

        foreach ($numbers as $number) {
            if (!is_int($number) && !is_float($number)) {
                throw new \RuntimeException('Arguments to sum must all be numbers');
            }
            $sum += $number;
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

    private function sort(Context $context): array|null
    {
        $value = $this->arg(0)?->evaluate($context);
        if (is_array($value)) {
            sort($value);
            return $value;
        }
        return null;
    }

    private function length(Context $context): int|null
    {
        $value = $this->arg(0)->evaluate($context);
        if (is_array($value)) {
            return count($value);
        } elseif (is_string($value)) {
            return mb_strlen($value, 'UTF-8');
        }
        return null;
    }

    private function reverse(Context $context): array|string|null
    {
        $value = $this->arg(0)->evaluate($context);
        if (is_array($value)) {
            return array_reverse($value);
        }
        if (is_string($value)) {
            $array = preg_split('//u', $value);
            return implode('', array_reverse($array));
        }
        return null;
    }

    private function sort_by(Context $context): array
    {
        $items = $this->args[0]?->evaluate($context);
        if (!is_array($items)) {
            throw new \RuntimeException('First argument to sort_by must resolve to an array');
        }
        if (!$this->args[1] instanceof ExpressionReference) {
            throw new \RuntimeException('Second argument to sort_by must be an ExpressionReference');
        }
        $sortFunction = $this->args[1]->getClosure();

        usort($items, fn ($a, $b) => $sortFunction($a) <=> $sortFunction($b));
        return $items;
    }

    private function items(Context $context): array
    {
        $items = $this->arg(0)->evaluate($context);
        $result = [];
        foreach ($items as $key => $value) {
            $result[] = [$key, $value];
        }
        return $result;
    }

    private function from_items(Context $context): array
    {
        $items = $this->arg(0)->evaluate($context);
        $result = [];
        foreach ($items as [$key, $value]) {
            $result[$key] = $value;
        }
        return $result;
    }

    private function not_null(Context $context): mixed
    {
        foreach ($this->args as $arg) {
            $value = $arg->evaluate($context);
            if ($value !== null) {
                return $value;
            }
        }
    }

    private function zip(Context $context): array
    {
        $operands = array_map(fn (NodeInterface $node) => $node->evaluate($context), $this->args);
        $i = 0;
        $result = [];
        while (true) {
            $row = [];
            foreach ($operands as $operand) {
                if (is_array($operand) && array_key_exists($i, $operand)) {
                    $row[] = $operand[$i];
                } else {
                    break 2;
                }
            }
            $result[] = $row;
            echo "+";
            $i++;
        }
        return $result;
    }

    private function max_by(Context $context): mixed
    {
        $items = $this->args[0]?->evaluate($context);
        if (!is_array($items)) {
            throw new \RuntimeException('First argument to max_by must resolve to an array');
        }
        if (!$this->args[1] instanceof ExpressionReference) {
            throw new \RuntimeException('Second argument to max_by must be an ExpressionReference');
        }
        $valueFunction = $this->args[1]->getClosure();

        $maximumElement = array_pop($items);
        $maximumValue = $valueFunction($maximumElement);
        foreach ($items as $item) {
            $newMaximumValue = $valueFunction($item);
            if ($newMaximumValue > $maximumValue) {
                $maximumValue = $newMaximumValue;
                $maximumElement = $item;
            }
        }
        return $maximumElement;
    }

    private function min_by(Context $context): mixed
    {
        $items = $this->args[0]?->evaluate($context);
        if (!is_array($items)) {
            throw new \RuntimeException('First argument to max_by must resolve to an array');
        }
        if (!$this->args[1] instanceof ExpressionReference) {
            throw new \RuntimeException('Second argument to max_by must be an ExpressionReference');
        }
        $valueFunction = $this->args[1]->getClosure();

        $minimumElement = array_pop($items);
        $minimumValue = $valueFunction($minimumElement);
        foreach ($items as $item) {
            $newMinimumValue = $valueFunction($item);
            if ($newMinimumValue < $minimumValue) {
                $minimumValue = $newMinimumValue;
                $minimumElement = $item;
            }
        }
        return $minimumElement;
    }

    private function map(Context $context): mixed
    {
        if (!$this->args[0] instanceof ExpressionReference) {
            throw new \RuntimeException('First argument to map must be an ExpressionReference');
        }

        $expressionFunction = $this->arg(0)->getClosure();
        $list = $this->args[1]->evaluate($context);

        if (!is_array($list) || !array_is_list($list)) {
            return null;
        }
        return array_map($expressionFunction, $list);
    }

    private function find_first(Context $context): int|null
    {
        $subject = $this->arg(0)->evaluate($context);
        $search = $this->args[1]->evaluate($context);
        $start = ($this->args[2] ?? null)?->evaluate($context) ?? 0;
        $stop = ($this->args[3] ?? null)?->evaluate($context);

        if ($start < -1 * mb_strlen($search, 'utf-8')) {
            $start = 0;
        }
        $result = mb_strpos($subject, $search, $start, 'utf-8');
        if (!is_int($result) || $result > ($stop ?? PHP_INT_MAX)) {
            return null;
        }
        return $result;
    }

    private function find_last(Context $context): int|null
    {
        $subject = $this->arg(0)->evaluate($context);
        $search = $this->args[1]->evaluate($context);
        $start = ($this->args[2] ?? null)?->evaluate($context) ?? 0;
        $stop = ($this->args[3] ?? null)?->evaluate($context);
        if ($start < -1 * mb_strlen($search, 'utf-8')) {
            $start = 0;
        }
        $result = mb_strrpos($subject, $search, $start, 'utf-8');
        if (!is_int($result) || $result > ($stop ?? PHP_INT_MAX)) {
            return null;
        }
        return $result;
    }

    private function trim(Context $context): string
    {
        $subject = $this->arg(0)->evaluate($context);
        $characters = $this->arg(1)?->evaluate($context);
        if (empty($characters)) {
            return mb_trim($subject);
        } else {
            return mb_trim($subject, $characters);
        }
    }

    private function trim_left(Context $context): string
    {
        $subject = $this->arg(0)->evaluate($context);
        $characters = $this->arg(1)?->evaluate($context);
        if (empty($characters)) {
            return mb_ltrim($subject);
        } else {
            return mb_ltrim($subject, $characters);
        }
    }

    private function trim_right(Context $context): string
    {
        $subject = $this->require(0)->evaluate($context);
        $characters = $this->arg(1)?->evaluate($context);
        if (empty($characters)) {
            return mb_rtrim($subject);
        } else {
            return mb_rtrim($subject, $characters);
        }
    }

    /**
     * @return list<string>
     */
    private function split(Context $context): array
    {
        $subject = $this->arg(0)->evaluate($context);
        $search = $this->args[1]->evaluate($context);

        $count = isset($this->args[2]) ? $this->args[2]->evaluate($context) : null;
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
    private function type(Context $context): string
    {
        $value = $this->arg(0)->evaluate($context);
        return match (true) {
            is_array($value) => array_is_list($value) ? "array" : "object",
            is_string($value) => "string",
            is_int($value), is_float($value) => "number",
            is_bool($value) => "boolean",
            default => "null",
        };
    }

    private function avg(Context $context): float|int
    {
        $operands = $this->arg(0)->evaluate($context);
        return array_sum($operands) / count($operands);
    }

    private function replace(Context $context): string
    {
        $subject = $this->arg(0)->evaluate($context);
        $old = $this->args[1]->evaluate($context);
        $new = $this->args[2]->evaluate($context);
        $limit = $this->arg(3)?->evaluate($context);
        if ($limit === 0) {
            return $subject;
        }
        $regex = "/" . preg_quote($old) . "/";
        return preg_replace($regex, $new, $subject, $limit ?? -1);
    }

    /**
     * @param Context $context
     * @return list<string>
     */
    private function keys(Context $context): array
    {
        $result = $this->arg(0)->evaluate($context);
        if (!is_array($result)) {
            throw new \RuntimeException('The argument to keys must be a list');
        }
        return array_keys($result);
    }

    private function join(Context $context): string
    {
        $separator = $this->args[0]?->evaluate($context);
        $parts = $this->args[1]?->evaluate($context);
        if (!is_string($separator) || !is_array($parts)) {
            throw new \RuntimeException('The arguments to join() must be a string and a list');
        }
        return implode($separator, $parts);
    }

    private function merge(Context $context)
    {
        $arrays = [];
        foreach ($this->args as $arg) {
            $arrays[] = $arg->evaluate($context);
        }
        return array_merge(...$arrays);
    }

    private function to_string(Context $context): string
    {
        $value = $this->arg(0)->evaluate($context);
        return is_string($value) ? $value : json_encode($value);
    }

    private function to_number(Context $context): float|null|int
    {
        $value = $this->arg(0)->evaluate($context);
        return is_numeric($value) ? floatval($value) : null;
    }

    private function to_array(Context $context)
    {
        $value = $this->arg(0)->evaluate($context);

        return is_array($value) && array_is_list($value) ? $value : [$value];
    }

    private function pad_right(Context $context): string
    {
        return str_pad(
            $this->arg(0)->evaluate($context),
            $this->arg(1)->evaluate($context),
            $this->arg(2)?->evaluate($context) ?? " "
        );
    }

    private function pad_left(Context $context): string
    {
        return str_pad(
            $this->arg(0)->evaluate($context),
            $this->arg(1)->evaluate($context),
            $this->arg(2)?->evaluate($context) ?? " ",
            STR_PAD_LEFT
        );
    }

    private function contains(Context $context): bool
    {
        $haystack = $this->arg(0)->evaluate($context);
        $needle = $this->args[1]->evaluate($context);
        return is_string($haystack)
            ? str_contains($haystack, $needle)
            : in_array($needle, $haystack)
        ;
    }

    /**
     * @return list<mixed>
     */
    private function array_values(Context $context): array
    {
        $value = $this->arg(0)->evaluate($context);
        if (!is_array($value)) {
            throw new \RuntimeException('The argument to array_values() must be an array');
        }
        return array_values($value);
    }

    private function abs(Context $context): int|float
    {
        $value = $this->arg(0)->evaluate($context);
        if (is_int($value) || is_float($value)) {
            return abs($value);
        }
        throw new \InvalidArgumentException('The argument to abs() must be a number');
    }

    private function min(Context $context): int|float|string
    {
        $value = $this->arg(0)->evaluate($context);
        if (is_array($value)) {
            return min($value);
        }
    }

    private function max(Context $context): int|float|string
    {
        $value = $this->arg(0)->evaluate($context);
        if (is_array($value)) {
            return max($value);
        }
    }

    private function starts_with(Context $context): bool
    {
        $haystack = $this->arg(0)->evaluate($context);
        $needle = $this->arg(1)->evaluate($context);
        return str_starts_with($haystack, $needle);
    }

    private function ends_with(Context $context): bool
    {
        $haystack = $this->arg(0)->evaluate($context);
        $needle = $this->arg(1)->evaluate($context);
        return str_ends_with($haystack, $needle);
    }

    private function ceil(Context $context): int
    {
        $value = $this->arg(0)->evaluate($context);
        if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException('The argument to ceil() must be a number');
        }
        return intval(ceil($value));
    }

    private function floor(Context $context): int
    {
        $value = $this->arg(0)->evaluate($context);
        if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException('The argument to floor() must be a number');
        }
        return intval(floor($value));
    }

    private function lower(Context $context): string
    {
        return strtolower($this->arg(0)->evaluate($context));
    }

    private function upper(Context $context): string
    {
        return strtoupper($this->arg(0)->evaluate($context));
    }
}
