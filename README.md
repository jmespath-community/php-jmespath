# jmespath-community/jmespath
`jmespath-community/jmespath` is a **PHP** implementation of the [JMESPath](https://jmespath.site/) spec.

This implementation is a full rewrite from scratch. It uses modern PHP language features and its implementation is compliant
with the Typescript library.

This means that:
- The lexer tokenizes expressions in exactly the same way (verified in CI / tests)
- The parser creates the exact same AST for each expression (verified in CI / tests)

# Usage

