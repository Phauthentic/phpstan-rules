# Forbidden Else Statements Rule

Forbids plain `else` branches in class methods whose `Full\Class\Name::methodName` matches any of the configured regex patterns. This matches the same targeting convention as rules such as **Method Must Return Type Rule** (`Fqcn::methodName` as a single string).

`elseif` is not checked (it is a different AST node). Prefer early returns, guard clauses, or `elseif` where appropriate.

If `patterns` is empty, the rule does nothing. If analysis is not inside a class method (no class or no enclosing function in scope), the rule does nothing—for example, `else` at file scope or in a global function is not matched.

To apply the rule to every method on a class, use a regex prefix on the method side of `::`, for example `/^App\\Module\\Foo::/` or `/^App\\Module\\Foo::.+$/`.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\ForbiddenElseStatementsRule
        arguments:
            patterns:
                - '/^App\\Presentation\\Http\\.*Controller::(handle|__invoke)$/'
                - '/^App\\Module\\.*::execute$/'
        tags:
            - phpstan.rules.rule
```

## Parameters

- `patterns`: List of PCRE regex strings. Each pattern is matched against `Full\Class\Name::methodName` for the enclosing method. If any pattern matches, an `else` in that method is reported.
