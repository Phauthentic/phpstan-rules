# Too Many Arguments Rule

Checks that methods do not have more than a specified number of arguments.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\TooManyArgumentsRule
        arguments:
            maxArguments: 3
        tags:
            - phpstan.rules.rule
```

## Parameters

- `maxArguments`: Maximum allowed number of method arguments (required).

