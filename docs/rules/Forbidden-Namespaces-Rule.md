# Forbidden Namespaces Rule

Enforces that certain namespaces cannot be declared in your codebase. This rule checks the `namespace` keyword and prevents the declaration of namespaces matching specified regex patterns, helping to enforce architectural constraints.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenNamespacesRule
        arguments:
            forbiddenNamespaces: [
                '/^App\\Legacy\\.*/',
                '/^App\\Deprecated\\.*/'
            ]
        tags:
            - phpstan.rules.rule
```

## Parameters

- `forbiddenNamespaces`: Array of regex patterns matching namespaces that are not allowed to be declared.

