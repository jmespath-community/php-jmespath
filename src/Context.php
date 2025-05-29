<?php

declare(strict_types=1);

namespace JmesPath;

namespace JmesPath;

use JmesPath\node\Variable;

final class Context
{
    private array $current = [];
    private array $scopeChain = [];

    public function __construct(array|string|null|float|bool $root)
    {
        $this->pushCurrent($root);
    }


    public function pushScope(array $scope): void
    {
        $this->scopeChain[] = $scope;
    }
    public function popScope(): void
    {
        array_pop($this->scopeChain);
    }
    public function getValue(Variable $variable): array|string|null|float|bool|int
    {
        for ($i = count($this->scopeChain) - 1; $i >= 0; $i--) {
            $result = $this->scopeChain[$i][$variable->name] ?? null;
            if (isset($result)) {
                return $result;
            }
        }
        return null;
    }

    public function pushCurrent(array|string|null|float|bool|int $value): void
    {
        $this->current[] = $value;
    }

    public function popCurrent(): array|string|null|float|bool|int
    {
        return array_pop($this->current);
    }

    public function current(): array|string|null|float|bool|int
    {
        return $this->current[count($this->current) - 1];
    }

    public function root(): array|string|null|float|bool|int
    {
        return $this->current[0];
    }
}
