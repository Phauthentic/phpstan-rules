# Catch Exception Of Type Not Allowed Rule

Ensures that specific exception types are not caught in catch blocks. This is useful for preventing the catching of overly broad exception types like `Exception`, `Error`, or `Throwable`.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\CatchExceptionOfTypeNotAllowedRule
        arguments:
            forbiddenExceptionTypes: ['Exception', 'Error', 'Throwable']
        tags:
            - phpstan.rules.rule
```

## Parameters

- `forbiddenExceptionTypes`: Array of exception type names that should not be caught.

