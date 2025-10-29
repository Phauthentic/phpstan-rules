# Classname Must Match Pattern Rule

Ensures that classes inside namespaces matching a given regex must have names matching at least one of the provided patterns.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassnameMustMatchPatternRule
        arguments:
            namespaceClassPatterns: [
                [
                    namespace: '/^App\\Service$/',
                    classPatterns: [
                        '/Class$/'
                    ]
                ]
            ]
        tags:
            - phpstan.rules.rule
```

## Parameters

- `namespaceClassPatterns`: Array of configurations, each containing:
  - `namespace`: Regex pattern to match namespaces
  - `classPatterns`: Array of regex patterns that class names must match

