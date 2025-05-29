<?php

namespace JmesPathCommunity\Tests;

use JmesPathCommunity\Lexer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Lexer::class)]
final class ModernLexerTest extends TestCase
{
    public static function validExpressionProvider(): iterable
    {
        foreach (CaseProvider::casesWithTokens() as $case) {
            yield [
                'expression' => $case['expression'],
                'tokens' => $case['tokens'],
            ];
        }
    }


    #[DataProvider('validExpressionProvider')]
    public function testHandlesValidExpressions(string $expression, array $tokens): void
    {
        $tokenstream = new Lexer()->tokenize($expression);


        self::assertSame($tokens, $tokenstream->toArray());
        //        , json_encode([
        //            'expected' => $tokens,
        //            'got' =>$tokenstream->toArray()
        //        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
