<?php

use JmesPath\Context;
use JmesPath\ModernParser;
use JmesPath\Tests\CaseProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ModernParser::class)]
final class ModernParserTest extends TestCase
{
    public static function expressionProvider(): iterable
    {
        foreach (CaseProvider::casesWithTokens() as $case) {
            yield [
                'expression' => $case['expression'],
                'expected' => $case['result'],
                'given' => $case['given']
            ];
        }
    }

    public static function astProvider(): iterable
    {
        foreach (CaseProvider::casesWithTokens() as $case) {
            yield [
                'expression' => $case['expression'],
                'expected' => $case['ast'],
            ];
        }
    }

    #[DataProvider('astProvider')]
    public function testCreateAst(string $expression, mixed $expected): void
    {
        $p = new ModernParser(false);

        $parsed = $p->parse($expression);

        self::assertEquals($expected, $parsed->toArray());
    }


    #[DataProvider('expressionProvider')]
    public function testEvaluatesCorrectly(string $expression, array|null $given, mixed $expected): void
    {
        //        echo "Evaluating $expression\n";
        $p = new ModernParser(false);

        $parsed = $p->parse($expression);
        $result = $parsed->evaluate(new Context($given));
        if ($result != $expected) {
            echo json_encode([
                'expr' => $expression,
                'given' => $given,
                'expected' => $expected,
                'expectedType' => gettype($expected),
                'ast' => $parsed->toArray(),
                'result' => $result,
                'resultType' => gettype($result),
            ], flags: JSON_PRETTY_PRINT);

            die();
        }
        self::assertEquals($expected, $result);
    }
}
