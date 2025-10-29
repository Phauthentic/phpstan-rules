# Method Signature Must Match Rule

Ensures that methods matching a class and method name pattern have a specific signature, including parameter types, names, and count.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\MethodSignatureMustMatchRule
        arguments:
            signaturePatterns:
                -
                    pattern: '/^MyClass::myMethod$/'
                    minParameters: 2
                    maxParameters: 2
                    signature:
                        -
                            type: 'int'
                            pattern: '/^id$/'
                        -
                            type: 'string'
                            pattern: '/^name$/'
        tags:
            - phpstan.rules.rule
```

## Parameters

- `pattern`: Regex for `ClassName::methodName`.
- `minParameters`/`maxParameters`: Minimum/maximum number of parameters.
- `signature`: List of expected parameter types and (optionally) name patterns.
- `visibilityScope`: Optional visibility scope (e.g., `public`, `protected`, `private`).

