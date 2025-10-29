# Class Must Be Final Rule

Ensures that classes matching specified patterns are declared as `final`.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustBeFinalRule
        arguments:
            patterns: ['/^App\\Service\\/']
            ignoreAbstractClasses: true
        tags:
            - phpstan.rules.rule
```

## Parameters

- `patterns`: Array of regex patterns to match against class names (with full namespace).
- `ignoreAbstractClasses`: Whether to ignore abstract classes (default: `true`).

