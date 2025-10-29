# Specification Docblock Rule

This directory contains test data for the `ClassMustHaveSpecificationDocblockRule`.

## Rule Configuration Example

```neon
# phpstan.neon
services:
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule
        arguments:
            patterns:
                - '/.*Facade$/'                         # All classes ending with "Facade"
                - '/.*Command$/'                        # All classes ending with "Command"
                - '/.*Handler$/'                        # All classes ending with "Handler"
            specificationHeader: 'Specification:'       # Optional: customize the header
            requireBlankLineAfterHeader: true           # Optional: require blank line (default: true)
            requireListItemsEndWithPeriod: false        # Optional: require periods (default: false)
        tags:
            - phpstan.rules.rule
```

## Configuration Options

- `patterns`: Array of regex patterns to match class names (required)
- `specificationHeader`: Header text to look for (default: `'Specification:'`)
- `requireBlankLineAfterHeader`: Require blank line after header (default: `true`)
- `requireListItemsEndWithPeriod`: Require list items end with period (default: `false`)

## Valid Specification Format

### Minimum Required Format
```php
/**
 * Specification:
 *
 * - Removes an item from the recommendation engine.
 */
class MyClass {}
```

### Multi-Line List Items
List items can span multiple lines:
```php
/**
 * Specification:
 *
 * - Removes an item from the recommendation engine
 *   and updates all related caches including user
 *   preferences and global recommendations.
 * - Validates the input data before processing
 *   and throws an exception if validation fails.
 */
class MyClass {}
```

### With Annotations
```php
/**
 * Specification:
 *
 * - Removes an item from the recommendation engine.
 *
 * @throws \Exception
 */
class MyClass {}
```

### With Additional Description and Annotations
```php
/**
 * Specification:
 *
 * - Removes an item from the recommendation engine.
 * - Updates the cache accordingly.
 *
 * Some additional description goes here.
 *
 * @throws \Exception
 * @return void
 */
class MyClass {}
```

## Invalid Formats

### Missing Specification Header
```php
/**
 * This is just a regular docblock.
 *
 * - Some item here.
 */
class MyClass {} // ERROR
```

### Missing Blank Line After Header
```php
/**
 * Specification:
 * - Missing blank line after header.
 */
class MyClass {} // ERROR
```

### Missing List Items
```php
/**
 * Specification:
 *
 * This has no list items.
 */
class MyClass {} // ERROR
```

