# Attribute Rule

Validates PHP 8+ attributes on classes, methods, and properties. This rule allows you to enforce which attributes are allowed, forbidden, or required on specific targets using regex patterns.

## Configuration Example

### Basic Usage

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                allowed:
                    # Controllers can only have Route attributes on the class
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
                forbidden:
                    # Controllers cannot have Deprecated attribute
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        attributes:
                            - '/^App\\Attribute\\Deprecated$/'
        tags:
            - phpstan.rules.rule
```

### Method Attributes

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                allowed:
                    # Methods ending with Action can only have Route attributes
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*Action$/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
                forbidden:
                    # Domain layer methods cannot have framework attributes
                    -
                        classPattern: '/^App\\Domain\\.*/'
                        methodPattern: '/.*/'
                        attributes:
                            - '/^Symfony\\.*/'
                            - '/^Doctrine\\.*/'
        tags:
            - phpstan.rules.rule
```

### Property Attributes

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                allowed:
                    # Entity properties can only have Doctrine attributes
                    -
                        classPattern: '/^App\\Entity\\.*/'
                        propertyPattern: '/.*/'
                        attributes:
                            - '/^Doctrine\\ORM\\Mapping\\.*$/'
                forbidden:
                    # Domain entity properties cannot have framework attributes
                    -
                        classPattern: '/^App\\Domain\\Entity\\.*/'
                        propertyPattern: '/.*/'
                        attributes:
                            - '/^Symfony\\.*/'
        tags:
            - phpstan.rules.rule
```

### Required Attributes

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                required:
                    # Controllers must have the AsController attribute
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        attributes:
                            - '/^Symfony\\Component\\HttpKernel\\Attribute\\AsController$/'
                    # All action methods must have a Route attribute
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*Action$/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
                    # All entity properties must have a Column attribute
                    -
                        classPattern: '/^App\\Entity\\.*/'
                        propertyPattern: '/.*/'
                        attributes:
                            - '/^Doctrine\\ORM\\Mapping\\Column$/'
        tags:
            - phpstan.rules.rule
```

### Full Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                allowed:
                    # Controllers can only have specific attributes on the class
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
                            - '/^Symfony\\Component\\HttpKernel\\Attribute\\AsController$/'
                    # Controller action methods can only have Route attributes
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*Action$/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
                    # Entity properties can only have Doctrine ORM attributes
                    -
                        classPattern: '/^App\\Entity\\.*/'
                        propertyPattern: '/.*/'
                        attributes:
                            - '/^Doctrine\\ORM\\Mapping\\.*$/'
                forbidden:
                    # Controllers cannot use Deprecated attribute
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        attributes:
                            - '/^Deprecated$/'
                            - '/^Internal$/'
                    # Domain layer cannot have any framework attributes on methods
                    -
                        classPattern: '/^App\\Domain\\.*/'
                        methodPattern: '/.*/'
                        attributes:
                            - '/^Symfony\\.*/'
                            - '/^Doctrine\\.*/'
                    # Domain layer cannot have any framework attributes on properties
                    -
                        classPattern: '/^App\\Domain\\.*/'
                        propertyPattern: '/.*/'
                        attributes:
                            - '/^Symfony\\.*/'
                            - '/^Doctrine\\.*/'
                required:
                    # Controllers must have AsController attribute
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        attributes:
                            - '/^Symfony\\Component\\HttpKernel\\Attribute\\AsController$/'
                    # All action methods must have Route attribute
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*Action$/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
        tags:
            - phpstan.rules.rule
```

## Parameters

The rule accepts a single `config` parameter with three optional sections:

### `allowed` Section

An array of rules defining which attributes are allowed. Each rule can have:

- `classPattern` (optional): Regex pattern to match class names (with full namespace).
- `methodPattern` (optional): Regex pattern to match method names.
- `propertyPattern` (optional): Regex pattern to match property names.
- `attributes` (required): Array of regex patterns to match allowed attribute FQCNs.

When a target matches a rule, only attributes matching the `attributes` patterns are allowed. Any other attribute will trigger an error.

### `forbidden` Section

An array of rules defining which attributes are forbidden. Each rule can have:

- `classPattern` (optional): Regex pattern to match class names (with full namespace).
- `methodPattern` (optional): Regex pattern to match method names.
- `propertyPattern` (optional): Regex pattern to match property names.
- `attributes` (required): Array of regex patterns to match forbidden attribute FQCNs.

When a target matches a rule and has an attribute matching any of the `attributes` patterns, an error is reported.

### `required` Section

An array of rules defining which attributes are required. Each rule can have:

- `classPattern` (optional): Regex pattern to match class names (with full namespace).
- `methodPattern` (optional): Regex pattern to match method names.
- `propertyPattern` (optional): Regex pattern to match property names.
- `attributes` (required): Array of regex patterns that must have at least one matching attribute.

When a target matches a rule, it must have at least one attribute matching each pattern in the `attributes` array. If any required attribute pattern is not satisfied, an error is reported.

## Pattern Matching Logic

### Target Matching

- `classPattern` alone: Matches attributes on the class itself.
- `methodPattern` alone: Matches methods in any class.
- `propertyPattern` alone: Matches properties in any class.
- `classPattern` + `methodPattern`: Matches methods in classes matching the class pattern.
- `classPattern` + `propertyPattern`: Matches properties in classes matching the class pattern.

### Attribute Matching

Attributes are matched by their fully qualified class name (FQCN). The regex patterns are matched against the FQCN as it appears in the code.

### Priority and Rule Combination

All three sections (`allowed`, `forbidden`, `required`) can be used simultaneously on the same target. Each section is processed independently:

1. **`forbidden`** is checked first - if an attribute matches a forbidden pattern, an error is reported
2. **`allowed`** is checked next (but only if the attribute wasn't already flagged as forbidden) - if an attribute doesn't match any allowed pattern, an error is reported  
3. **`required`** is checked separately - if the target doesn't have at least one attribute matching each required pattern, an error is reported

This means you can:
- Allow only certain attributes AND require specific ones
- Forbid specific attributes while allowing others AND requiring some
- Have overlapping patterns where forbidden takes precedence over allowed

## Use Cases

### Combining Allowed, Forbidden, and Required on the Same Target

You can apply all three rule types to the same class or method. This example ensures controller action methods:
- Can ONLY have `Route` or `Cache` attributes (allowed)
- Must NOT use the deprecated Sensio `Route` annotation (forbidden)
- MUST have at least one `Route` attribute (required)

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                allowed:
                    # Controller actions can ONLY have Route or Cache attributes
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*Action$/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
                            - '/^Symfony\\Component\\HttpKernel\\Attribute\\Cache$/'
                forbidden:
                    # But specifically forbid the deprecated Sensio Route annotation
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*Action$/'
                        attributes:
                            - '/^Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Route$/'
                required:
                    # And every action MUST have at least one Route attribute
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*Action$/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
        tags:
            - phpstan.rules.rule
```

This configuration would:
- **Pass**: A method with `#[Route('/path')]` 
- **Fail (not allowed)**: A method with `#[Route('/path'), #[SomeOtherAttribute]]`
- **Fail (forbidden)**: A method with `#[Sensio\Route('/path')]`
- **Fail (required missing)**: A method with only `#[Cache(60)]` but no `Route`

### Enforcing Framework Attribute Usage in Controllers

Ensure controllers only use approved routing attributes:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                allowed:
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
                            - '/^Symfony\\Component\\HttpKernel\\Attribute\\.*$/'
        tags:
            - phpstan.rules.rule
```

### Keeping Domain Layer Clean

Prevent framework-specific attributes in your domain layer:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                forbidden:
                    -
                        classPattern: '/^App\\Domain\\.*/'
                        methodPattern: '/.*/'
                        attributes:
                            - '/^Symfony\\.*/'
                            - '/^Doctrine\\.*/'
                            - '/^Laravel\\.*/'
                    -
                        classPattern: '/^App\\Domain\\.*/'
                        propertyPattern: '/.*/'
                        attributes:
                            - '/^Symfony\\.*/'
                            - '/^Doctrine\\.*/'
                            - '/^Laravel\\.*/'
        tags:
            - phpstan.rules.rule
```

### Enforcing ORM Attributes on Entities

Ensure entity properties only use Doctrine ORM mapping attributes:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                allowed:
                    -
                        classPattern: '/^App\\Entity\\.*/'
                        propertyPattern: '/.*/'
                        attributes:
                            - '/^Doctrine\\ORM\\Mapping\\.*$/'
        tags:
            - phpstan.rules.rule
```

### Requiring Route Attributes on Controller Actions

Ensure all controller action methods have a Route attribute:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                required:
                    -
                        classPattern: '/^App\\Controller\\.*/'
                        methodPattern: '/.*Action$/'
                        attributes:
                            - '/^Symfony\\Component\\Routing\\Annotation\\Route$/'
        tags:
            - phpstan.rules.rule
```

### Requiring Entity Attributes on Domain Entities

Ensure all entity classes have the Entity attribute:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\AttributeRule
        arguments:
            config:
                required:
                    -
                        classPattern: '/^App\\Entity\\.*/'
                        attributes:
                            - '/^Doctrine\\ORM\\Mapping\\Entity$/'
        tags:
            - phpstan.rules.rule
```

## Error Messages

The rule produces three types of error messages:

- **Forbidden attribute**: `Attribute {attribute} is forbidden on {type} {name}.`
- **Not in allowed list**: `Attribute {attribute} is not in the allowed list for {type} {name}. Allowed patterns: {patterns}`
- **Missing required attribute**: `Missing required attribute matching pattern {pattern} on {type} {name}.`

Where `{type}` is one of `class`, `method`, or `property`.
