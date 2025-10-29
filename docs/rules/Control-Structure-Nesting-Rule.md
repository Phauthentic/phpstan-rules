# Control Structure Nesting Rule

Ensures that the nesting level of `if` and `try-catch` statements does not exceed a specified limit.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\ControlStructureNestingRule
        arguments:
            maxNestingLevel: 2
        tags:
            - phpstan.rules.rule
```

## Parameters

- `maxNestingLevel`: Maximum allowed nesting level for control structures (required).

