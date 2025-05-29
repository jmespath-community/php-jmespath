<?php

declare(strict_types=1);

namespace JmesPathCommunity\node;

use JmesPathCommunity\Context;
use JmesPathCommunity\NodeInterface;

final readonly class Slice implements NodeInterface
{
    /**
     * @param int|null $start
     * @param negative-int|positive-int|null $stop
     * @param int $step
     */
    public function __construct(private int|null $start = null, private int|null $stop = null, private int|null $step = null)
    {
    }

    public function evaluate(Context $context): array|null|string
    {
        $list = $context->current();
        $resultAsString = is_string($list);
        if ($resultAsString) {
            $list = preg_split('//u', $list, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!is_array($list)) {
            return null;
        }
        $length = count($list);

        $step = $this->step ?? 1;
        $start = !isset($this->start)
            ? ($step > 0 ? 0 : $length - 1)
            : ($this->start < 0 ? $length + $this->start : min($this->start, $length - 1));
        if (isset($this->stop)) {
            $stop = $this->stop < 0 ? max($length + $this->stop, -1) : min($this->stop, $length);
        } else {
            $stop = ($step < 0 ? -1 : $length);
        }

        $result = [];




        if ($step < 0) {
            $i = $start;
            while ($i > $stop) {
                $result[] = $list[$i];
                $i += $step;
            }
        } else {
            $i = $start;
            while ($i < $stop) {
                $result[] = $list[$i];
                $i += $step;
            }
        }

        return $resultAsString ? implode('', $result) : $result;
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'step' => $this->step,
            'stop' => $this->stop,
            'type' => 'Slice',

        ];
    }
}
