# Class Must Be Readonly Rule

Ensures that classes matching specified patterns are declared as `readonly`.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustBeReadonlyRule
        arguments:
            patterns: ['/^App\\Controller\\/']
        tags:
            - phpstan.rules.rule
```

## Parameters

- `patterns`: Array of regex patterns to match against class names (with full namespace).

