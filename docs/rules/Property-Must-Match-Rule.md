# Property Must Match Rule

Ensures that classes matching specified patterns have properties with expected names, types, and visibility scopes. Can optionally enforce that matching classes must have certain properties.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule
        arguments:
            propertyPatterns:
                -
                    classPattern: '/^.*Controller$/'
                    properties:
                        -
                            name: 'id'
                            type: 'int'
                            visibilityScope: 'private'
                            required: true
                        -
                            name: 'repository'
                            type: 'RepositoryInterface'
                            visibilityScope: 'private'
                            required: true
                -
                    classPattern: '/^.*Service$/'
                    properties:
                        -
                            name: 'logger'
                            type: 'LoggerInterface'
                            visibilityScope: 'private'
                            required: false
        tags:
            - phpstan.rules.rule
```

## Parameters

- `propertyPatterns`: Array of class pattern configurations.
  - `classPattern`: Regex to match against class names.
  - `properties`: Array of property rules for matching classes.
    - `name`: The exact property name to check.
    - `type`: Optional expected type (supports scalar types, class names, nullable types like `?int`).
    - `visibilityScope`: Optional visibility scope (`public`, `protected`, `private`).
    - `required`: Optional boolean (default: `false`). When `true`, enforces that matching classes must have this property.
    - `nullable`: Optional boolean (default: `false`). When `true`, allows both the specified type and its nullable variant (e.g., both `int` and `?int`).

## Required Properties

When the `required` parameter is set to `true`, the rule will check if classes matching the pattern actually have the specified property. If a matching class is missing the required property, an error will be reported.

### Example with Required Properties

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule
        arguments:
            propertyPatterns:
                -
                    classPattern: '/^App\\Entity\\.*$/'
                    properties:
                        -
                            name: 'id'
                            type: 'int'
                            visibilityScope: 'private'
                            required: true
                        -
                            name: 'createdAt'
                            type: 'DateTimeImmutable'
                            visibilityScope: 'private'
                            required: true
        tags:
            - phpstan.rules.rule
```

In this example, any class in the `App\Entity` namespace must have a private `id` property of type `int` and a private `createdAt` property of type `DateTimeImmutable`.

## Optional Property Validation

When `required` is `false` (or omitted), the rule will only validate type and visibility if the property exists. This is useful for optional properties that should follow certain conventions when present.

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule
        arguments:
            propertyPatterns:
                -
                    classPattern: '/^.*Service$/'
                    properties:
                        -
                            name: 'logger'
                            type: 'Psr\Log\LoggerInterface'
                            visibilityScope: 'private'
                            required: false
        tags:
            - phpstan.rules.rule
```

In this example, if a Service class has a `logger` property, it must be of type `Psr\Log\LoggerInterface` and private, but the property itself is not required.

## Nullable Properties

When `nullable` is set to `true`, the rule will accept both the exact type and its nullable variant. This is useful when you want to allow properties to be either nullable or non-nullable.

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule
        arguments:
            propertyPatterns:
                -
                    classPattern: '/^.*Handler$/'
                    properties:
                        -
                            name: 'id'
                            type: 'int'
                            visibilityScope: 'private'
                            nullable: true
        tags:
            - phpstan.rules.rule
```

In this example, Handler classes can have an `id` property typed as either `int` or `?int`. Both are valid when `nullable: true`.
