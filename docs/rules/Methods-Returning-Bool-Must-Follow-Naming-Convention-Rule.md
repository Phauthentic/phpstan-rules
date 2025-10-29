# Methods Returning Bool Must Follow Naming Convention Rule

Ensures that methods returning boolean values follow a specific naming convention. By default, boolean methods should start with `is`, `has`, `can`, `should`, `was`, or `will`.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\MethodsReturningBoolMustFollowNamingConventionRule
        arguments:
            regex: '/^(is|has|can|should|was|will)[A-Z_]/'
        tags:
            - phpstan.rules.rule
```

## Parameters

- `regex`: Regular expression pattern that method names must match (default: `/^(is|has|can|should|was|will)[A-Z_]/`).

