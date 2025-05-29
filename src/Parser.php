<?php

declare(strict_types=1);

namespace JmesPathCommunity;

use JmesPathCommunity\node\AndExpression;
use JmesPathCommunity\node\Arithmetic;
use JmesPathCommunity\node\Binding;
use JmesPathCommunity\node\Comparator;
use JmesPathCommunity\node\CurrentNode;
use JmesPathCommunity\node\ExpressionReference;
use JmesPathCommunity\node\Field;
use JmesPathCommunity\node\FilterProjection;
use JmesPathCommunity\node\FunctionCallNode;
use JmesPathCommunity\node\Identity;
use JmesPathCommunity\node\Index;
use JmesPathCommunity\node\IndexExpression;
use JmesPathCommunity\node\LetExpression;
use JmesPathCommunity\node\LiteralNode;
use JmesPathCommunity\node\MultiselectHash;
use JmesPathCommunity\node\MultiselectList;
use JmesPathCommunity\node\NotExpression;
use JmesPathCommunity\node\OrExpression;
use JmesPathCommunity\node\Pipe;
use JmesPathCommunity\node\Projection;
use JmesPathCommunity\node\RootNode;
use JmesPathCommunity\node\Slice;
use JmesPathCommunity\node\Subexpression;
use JmesPathCommunity\node\UnaryExpression;
use JmesPathCommunity\node\ValueProjection;
use JmesPathCommunity\node\Variable;

final class Parser
{
    private TokenStream $tokens;
    public function __construct()
    {
    }

    public function parse(string $expression): NodeInterface
    {
        try {
            $this->tokens = new Lexer()->tokenize($expression);
        } catch (\Exception $e) {
            var_dump($expression);
            die();
        }
        return $this->parseExpression(0);
    }

    public function parseExpression(int $rightBindingPower = 0): NodeInterface
    {
        $leftToken = $this->tokens->advance();
        $left = $this->parsePrefix($leftToken);

        while ($rightBindingPower < $this->tokens->current()->type->bindingPower()) {
            $left = $this->parseInfix($left);
        }

        return $left;
    }

    private function parseSlice(int|null $start = null): Slice
    {
        $params = [];
        if (isset($start)) {
            $params['start'] = $start;
        }

        if ($this->tokens->peek(TokenType::Number)) {
            $params['stop'] = $this->tokens->expect(TokenType::Number)->value;
        }


        if ($this->tokens->match(TokenType::Colon)) {
            if ($this->tokens->current()->type !== TokenType::Rbracket) {
                $params['step'] = $this->tokens->expect(TokenType::Number)->value;
            }
        }

        $this->tokens->expect(TokenType::Rbracket);
        return new Slice(...$params);
    }

    private function parseIndexExpression(): Index|Slice
    {
        if ($this->tokens->match(TokenType::Colon)) {
            // Slice expression like [:]
            return $this->parseSlice();
        }


        $number = $this->tokens->expect(TokenType::Number)->value;
        if ($this->tokens->match(TokenType::Colon)) {
            // Handle [start:stop] or [start:stop:step]
            return $this->parseSlice(start: $number);
        }

        $this->tokens->expect(TokenType::Rbracket);
        return new Index($number);
    }
    private function parseBracketExpression(NodeInterface $left): NodeInterface
    {
        // [*
        if ($this->tokens->match(TokenType::Star)) {
            $this->tokens->expect(TokenType::Rbracket);
            return new Projection($left, $this->parseProjectionRHS(TokenType::Star->bindingPower()));
        }


        return $this->projectIfSlice($left);
    }

    private function projectIfSlice(NodeInterface $left): IndexExpression|Projection
    {
        $right = $this->parseIndexExpression();
        $indexExpression = new IndexExpression($left, $right);
        if ($right instanceof Slice) {
            return new Projection(
                left: $indexExpression,
                right: $this->parseProjectionRHS(TokenType::Star->bindingPower()),
            );
        }
        return $indexExpression;
    }
    private function parsePrefix(Token $token): NodeInterface
    {
        return match ($token->type) {
            TokenType::Variable => new Variable($token->value),
            TokenType::Literal => new LiteralNode($token->value),
            TokenType::UnquotedIdentifier => ($token->value === 'let' && $this->tokens->peek(TokenType::Variable))
                ? $this->parseLetExpression()
                : new Field($token->value),
            TokenType::QuotedIdentifier => $this->tokens->peek(TokenType::Lparen)
                ? throw new \RuntimeException('Syntax error: quoted identifier not allowed for function names')
                : new Field($token->value),
            TokenType::Not => new NotExpression($this->parseExpression($token->bindingPower())),
            TokenType::Minus => new UnaryExpression($this->parseExpression($token->bindingPower()), UnaryOperator::Minus),
            TokenType::Plus => new UnaryExpression($this->parseExpression($token->bindingPower()), UnaryOperator::Plus),
            TokenType::Star => new ValueProjection(new Identity(), $this->parseProjectionRHS($token->bindingPower())),
            TokenType::Filter => $this->parseFilter(new Identity()),
            TokenType::Lbrace => $this->parseMultiselectHash(),
            TokenType::Flatten => new Projection(
                new UnaryExpression(new Identity(), UnaryOperator::Flatten),
                $this->parseProjectionRHS($token->bindingPower())
            ),
            TokenType::Lbracket => $this->parsePrefixLBracket(),
            TokenType::Current => new CurrentNode(),
            TokenType::Root => new RootNode(),
            TokenType::Expref => new ExpressionReference($this->parseExpression($token->bindingPower())),
            TokenType::Lparen => $this->parseGroupedExpression(),
            default => throw new \Exception("Unexpected token prefix: {$token->type->name} - {$token->value}" . $this->tokens->dump()),
        };
    }

    private function parseFilter(NodeInterface $left): FilterProjection
    {
        $filter = $this->parseExpression();
        $this->tokens->expect(TokenType::Rbracket);
        if ($this->tokens->peek(TokenType::Flatten)) {
            $right = new Identity();
        } else {
            $right = $this->parseProjectionRHS(TokenType::Filter->bindingPower());
        }
        return new FilterProjection($left, $right, $filter);
    }

    private function parseInfix(NodeInterface $left): NodeInterface
    {
        $current = $this->tokens->advance();
        return match ($current->type) {
            TokenType::Dot => $this->parseDot($left),
            TokenType::Pipe => new Pipe($left, $this->parseExpression($current->bindingPower())),
            TokenType::Or => new OrExpression($left, $this->parseExpression($current->bindingPower())),
            TokenType::And => new AndExpression($left, $this->parseExpression($current->bindingPower())),
            TokenType::Lparen => $this->parseFunctionCall($left),
            TokenType::Filter => $this->parseFilter($left),
            TokenType::Flatten => new Projection(new UnaryExpression($left, UnaryOperator::Flatten), $this->parseProjectionRHS($current->bindingPower())),
            TokenType::EQ, TokenType::NE, TokenType::GT, TokenType::GTE, TokenType::LT, TokenType::LTE =>
                new Comparator($left, ComparatorType::fromTokenType($current->type), $this->parseExpression($current->bindingPower())),
            TokenType::Plus, TokenType::Minus, TokenType::Multiply, TokenType::Star, TokenType::Divide, TokenType::Modulo, TokenType::Div =>
                new Arithmetic($left, ArithmethicType::fromTokenType($current->type), $this->parseExpression($current->bindingPower())),
            TokenType::Lbracket => $this->parseBracketExpression($left),
            default => throw new \Exception("Unexpected infix token: {$current->type->name}")
        };
    }

    private function parseGroupedExpression(): NodeInterface
    {
        $expr = $this->parseExpression();
        $this->tokens->expect(TokenType::Rparen);
        return $expr;
    }

    private function parseFunctionCall(NodeInterface $left): NodeInterface
    {
        if (!$left instanceof Field) {
            throw new \InvalidArgumentException('Function must be an identifier');
        }
        $args = [];
        while (!$this->tokens->match(TokenType::Rparen)) {
            $args[] = $this->parseExpression();
            $this->tokens->match(TokenType::Comma);
        }
        return new FunctionCallNode($left->value, $args);
    }
    private function parsePrefixLBracket(): NodeInterface
    {
        if ($this->tokens->peek(TokenType::Number, TokenType::Colon)) {
            return $this->projectIfSlice(new Identity());
        }

        if ($this->tokens->matchChain(TokenType::Star, TokenType::Rbracket)) {
            return new Projection(new Identity(), $this->parseProjectionRHS(TokenType::Star->bindingPower()));
        }

        // Multi-select list
        $children = [];
        while (!$this->tokens->match(TokenType::Rbracket)) {
            $children[] = $this->parseExpression();
            $this->tokens->match(TokenType::Comma);
        }
        return new MultiselectList($children);
    }

    private function parseProjectionRHS(int $rightBindingPower): NodeInterface
    {
        if ($this->tokens->peek(TokenType::Lbracket, TokenType::Filter)) {
            return $this->parseExpression($rightBindingPower);
        }
        if ($this->tokens->match(TokenType::Dot)) {
            return $this->parseDotRHS($rightBindingPower);
        }

        $current = $this->tokens->current();
        if ($current->bindingPower() < 10) {
            return new Identity();
        }
        throw new \RuntimeException("Syntax error, unexpected token: {$current->type->name} at {$current->position}");
    }
    private function parseDot(NodeInterface $left): NodeInterface
    {
        // Handle .*
        if ($this->tokens->match(TokenType::Star)) {
            return new ValueProjection($left, $this->parseProjectionRHS(TokenType::Dot->bindingPower()));
        } else {
            return new Subexpression($left, $this->parseDotRHS(TokenType::Dot->bindingPower()));
        }
    }

    private function parseDotRHS(int $rightBindingPower): NodeInterface
    {
        if ($this->tokens->peek(TokenType::UnquotedIdentifier, TokenType::QuotedIdentifier, TokenType::Star)) {
            return $this->parseExpression($rightBindingPower);
        }

        if ($this->tokens->match(TokenType::Lbracket)) {
            return $this->parseMultiselectList();
        }

        if ($this->tokens->match(TokenType::Lbrace)) {
            return $this->parseMultiselectHash();
        }

        $current = $this->tokens->current();
        throw new \RuntimeException("Syntax error, unexpected token: {$current->type->name} at {$current->position}");
    }

    private function parseMultiselectList(): MultiselectList
    {
        // Multi-select list
        $children = [];
        while (!$this->tokens->match(TokenType::Rbracket)) {
            $children[] = $this->parseExpression();
            $this->tokens->match(TokenType::Comma);
        }
        return new MultiselectList($children);
    }

    private function parseMultiselectHash(): MultiselectHash
    {
        $children = [];
        while (!$this->tokens->match(TokenType::Rbrace)) {
            $key = $this->tokens->expect(TokenType::QuotedIdentifier, TokenType::UnquotedIdentifier)->value;
            $this->tokens->expect(TokenType::Colon);
            $value = $this->parseExpression();
            $this->tokens->match(TokenType::Comma);
            $children[$key] = $value;
        }
        return new MultiselectHash($children);
    }

    private function parseLetExpression(): LetExpression
    {
        $bindings = [];
        while (!$this->tokens->peek(TokenType::UnquotedIdentifier)
            && !($this->tokens->current()->value === 'in')
        ) {
            $variable = new Variable($this->tokens->expect(TokenType::Variable)->value);
            $this->tokens->expect(TokenType::Assign);
            $bindings[] = $this->parseBinding($variable);
            $this->tokens->match(TokenType::Comma);
        }
        $this->tokens->expect(TokenType::UnquotedIdentifier);
        return new LetExpression($this->parseExpression(), $bindings);
    }

    private function parseBinding(NodeInterface $left): Binding
    {
        if (!$left instanceof Variable) {
            throw new \RuntimeException('Expected variable on left side of assignment');
        }
        return new Binding($left, $this->parseExpression());
    }
}
