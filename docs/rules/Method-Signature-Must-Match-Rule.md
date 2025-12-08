# Method Signature Must Match Rule

Ensures that methods matching a class and method name pattern have a specific signature, including parameter types, names, and count. Optionally enforces that matching classes must implement the specified method.

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
- `required`: Optional boolean (default: `false`). When `true`, enforces that any class matching the pattern must implement the method with the specified signature.

## Required Methods

When the `required` parameter is set to `true`, the rule will check if classes matching the pattern actually implement the specified method. If a matching class is missing the method, an error will be reported with details about the expected signature.

### Example with Required Method

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\MethodSignatureMustMatchRule
        arguments:
            signaturePatterns:
                -
                    pattern: '/^.*Controller::execute$/'
                    minParameters: 1
                    maxParameters: 1
                    signature:
                        -
                            type: 'Request'
                            pattern: '/^request$/'
                    visibilityScope: 'public'
                    required: true
        tags:
            - phpstan.rules.rule
```

In this example, any class ending with "Controller" must implement a public `execute` method that takes exactly one parameter of type `Request` named `request`.
