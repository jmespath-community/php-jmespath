<?php

declare(strict_types=1);

namespace JmesPathCommunity\Tests;

use RecursiveDirectoryIterator;

final readonly class CaseProvider
{
    public static function cases(): iterable
    {
        $dir = __DIR__ . '/../vendor/jmespath-community/jmespath.test/tests';
        $cases = [];

        foreach (new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS) as $file) {
            if ($file->isFile() && $file->getExtension() === 'json') {
                $data = file_get_contents($file->getPathname());
                $json = json_decode($data, true, JSON_THROW_ON_ERROR);
                foreach ($json as $set) {
                    foreach ($set['cases'] ?? [] as $case) {
                        $cases[] = [
                            'given' => $set['given'],
                            ...$case,
                        ];
                    }
                }
            }
        }
        return $cases;
    }

    public static function validCases(): iterable
    {
        foreach (self::cases() as $case) {
            if (isset($case['result'])) {
                //                if ($case['expression'] !== 'foo[*].bar[0]'
                //                    || $case['given'] != ['foo' => [
                //                        ['bar' => []],
                //                        ['bar' => []],
                //                        ['bar' => []],
                //                    ]]
                //                ) {
                //                    continue;
                //                }
                yield $case;
            }
        }
    }

    /**
     * @return iterable<array{expression: string, ast: mixed, tokens: list<array{start: non-negative-int, type: string, value: mixed}>, given: mixed, result: mixed}>
     */
    public static function casesWithTokens(): iterable
    {
        // Set up process.
        // Get path to node.
        $node = shell_exec('which node') ?? '/home/sam/.nvm/versions/node/v22.14.0/bin/node';
        if (($node === false) || ($node === null)) {
            throw new \RuntimeException('Could not find path to node');
        }
        $proc = proc_open([trim($node), __DIR__ . '/tokenize.mjs'], [
            ["pipe", "r"],
            ["pipe", "w"],
            ["pipe", "w"]
        ], $pipes);

        foreach (self::validCases() as $i => $case) {
            $writeResult = fwrite($pipes[0], json_encode($case['expression']) . "\n");
            if ($writeResult === false) {
                var_dump(stream_get_contents($pipes[2]));
                die();
            }
            fflush($pipes[0]);
            $result = json_decode(fgets($pipes[1]), associative: true, flags: JSON_THROW_ON_ERROR);
            if ($result['expression'] !== $case['expression']) {
                throw new \RuntimeException("Expression got malformed: {$result['expression']} -- {$case['expression']}");
            }

            $recursiveSort = function (array $array, $recursor) {
                ksort($array);
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $array[$key] = $recursor($value, $recursor);
                    }
                }
                return $array;
            };

            yield [
                ...$case,
                'ast' => $recursiveSort($result['ast'], $recursiveSort),
                'tokens' => array_map(fn (array $token) => [
                    // We're ignoring start for now since it is hard to calculate for JS due to unicode multi plane quantum physics.
                    // 'start' => $token['start'],
                    'type' => $token['type'],
                    'value' => $token['value'],
                ], $result['tokens'])
            ];
        }
        proc_close($proc);
    }
}
