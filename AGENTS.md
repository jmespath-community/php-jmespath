# jmespath-community/jmespath.php

PHP implementation of JMESPath query language for JSON documents.
Full rewrite from scratch, validated against the TypeScript reference.

## Directory Structure

```
src/              # Source code (namespace JmesPathCommunity\)
src/node/         # AST node implementations (26 files)
tests/            # Test suite (namespace JmesPathCommunity\Tests\)
vendor/           # Composer dependencies
.github/workflows/ # CI pipeline
```

## Core Source Files

| File | Purpose |
|---|---|
| `Lexer.php` | Tokenizes JMESPath expressions via state machine |
| `Token.php` | Immutable value object for a single token |
| `TokenType.php` | Enum of all token types with Pratt binding powers |
| `TokenStream.php` | Wrapper over token array with position tracking |
| `Parser.php` | Pratt parser producing AST nodes |
| `NodeInterface.php` | Interface: `evaluate(Context)` + `toArray()` |
| `Context.php` | Runtime context: current value stack + variable scopes |
| `Reader.php` | Character buffer with unicode-safe lookahead/consume |
| `State.php` | Lexer state enum (maps first char → token type) |
| `ArithmethicType.php` | Enum: arithmetic operation variants |
| `ComparatorType.php` | Enum: comparison operation variants |
| `UnaryOperator.php` | Enum: Flatten, Minus, Plus |
| `UnterminatedStringException.php` | Custom runtime exception |

## AST Nodes (src/node/)

| Node | Type | Purpose |
|---|---|---|
| `Identity` | Identity | Returns current context value |
| `Field` | Field | Object key access |
| `Subexpression` | Subexpression | `left.right` chained access |
| `Index` | Index | Numeric index `[n]` |
| `IndexExpression` | IndexExpression | `left[index]` |
| `Slice` | Slice | `[start:stop:step]` |
| `Pipe` | Pipe | `left \| right` |
| `Projection` | Projection | Wildcard/flatten projections |
| `ValueProjection` | ValueProjection | `.*` projections |
| `FilterProjection` | FilterProjection | `[?condition]` |
| `LiteralNode` | Literal | Backtick literals |
| `CurrentNode` | Current | `@` |
| `RootNode` | Root | `$` |
| `Variable` | Variable | `$var` |
| `LetExpression` | LetExpression | `let $x = expr` |
| `Binding` | Binding | Let variable binding |
| `FunctionCallNode` | FunctionCall | 40+ functions |
| `NotExpression` | Not | `!` |
| `OrExpression` | Or | `\|\|` |
| `AndExpression` | And | `&&` |
| `Comparator` | Comparator | `== != < > <= >=` |
| `Arithmetic` | Arithmetic | `+ - * / % //` |
| `UnaryExpression` | UnaryExpression | Unary `-` / `+` |
| `MultiselectList` | MultiselectList | `[a, b]` |
| `MultiselectHash` | MultiselectHash | `{k: v}` |
| `ExpressionReference` | ExpressionReference | `&expr` |

## Testing

- **`CaseProvider.php`** — Loads test cases from `vendor/jmespath-community/jmespath.test/tests/` JSON files
- **`tokenize.mjs`** — Node.js helper that tokenizes + compiles expressions via the TS reference, cross-validating PHP output
- **`LexerTest.php`** — Verifies tokenization matches TypeScript implementation
- **`ParserTest.php`** — Verifies AST structure and evaluation results match expected values

## CI (GitHub Actions)

- Runs on PHP 8.4 and 8.5
- `composer install` + `npm ci` + `vendor/bin/phpunit`

## Configuration & Tooling

- **PHP 8.4+** required, `ext-mbstring` and `ext-ctype` required
- **PSR-4**: `JmesPathCommunity\` → `src/`, `JmesPathCommunity\Tests\` → `tests/`
- **PHPStan** level 9 on `src/` and `tests/`
- **ECS** (Easy Coding Standard) with PSR-12 + no unused imports
- **PHPUnit 12** with `phpunit.xml.dist`

## Key Conventions

- All AST node classes are `final readonly`
- All source files use `declare(strict_types=1)`
- No docblock comments (project convention observed in all existing files)
- No unused imports (enforced by CI)

## Commands

| Action | Command |
|---|---|
| Run tests | `vendor/bin/phpunit` |
