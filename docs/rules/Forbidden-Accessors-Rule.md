# Forbidden Accessors Rule

Forbids public and/or protected getters (`getXxx()`) and setters (`setXxx()`) on classes matching specified patterns. This is useful for enforcing immutability or encapsulation in domain entities or value objects.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule
        arguments:
            classPatterns:
                - '/^App\\Domain\\.*Entity$/'
                - '/^App\\Domain\\.*ValueObject$/'
            forbidGetters: true
            forbidSetters: true
            visibility:
                - public
        tags:
            - phpstan.rules.rule
```

## Parameters

- `classPatterns`: Array of regex patterns to match against class FQCNs.
- `forbidGetters`: Whether to forbid `getXxx()` methods (default: `true`).
- `forbidSetters`: Whether to forbid `setXxx()` methods (default: `true`).
- `visibility`: Array of visibilities to check. Valid values are `public` and `protected` (default: `['public']`).

## Use Cases

### Forbid Setters Only (Immutable Objects)

To enforce immutability while still allowing getters:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule
        arguments:
            classPatterns:
                - '/^App\\Domain\\.*Entity$/'
            forbidGetters: false
            forbidSetters: true
            visibility:
                - public
                - protected
        tags:
            - phpstan.rules.rule
```

### Forbid All Accessors (Tell, Don't Ask)

To enforce the "Tell, Don't Ask" principle on domain entities:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule
        arguments:
            classPatterns:
                - '/^App\\Domain\\Model\\/'
            forbidGetters: true
            forbidSetters: true
            visibility:
                - public
                - protected
        tags:
            - phpstan.rules.rule
```
