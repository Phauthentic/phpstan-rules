# Class Must Have Specification Docblock Rule

Ensures that classes matching specified patterns have a properly formatted docblock with a "Specification:" section. The specification must contain a list of items, and optionally allows annotations and additional text.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule
        arguments:
            patterns:
                - '/.*Facade$/'        # All classes ending with "Facade"
                - '/.*Command$/'       # All classes ending with "Command"
                - '/.*Handler$/'       # All classes ending with "Handler"
            specificationHeader: 'Specification:'  # Optional: customize the header text
            requireBlankLineAfterHeader: true       # Optional: require blank line after header (default: true)
            requireListItemsEndWithPeriod: false    # Optional: require list items to end with period (default: false)
        tags:
            - phpstan.rules.rule
```

## Valid Docblock Formats

### Default Format

```php
/**
 * Specification:
 *
 * - Removes an item from the recommendation engine.
 * - Updates the cache accordingly.
 */
class MyFacade {}
```

### With Periods Required

```php
/**
 * Specification:
 *
 * - Removes an item from the recommendation engine.
 * - Updates the cache accordingly.
 */
class MyFacade {}
```

### Without Blank Line (requireBlankLineAfterHeader: false)

```php
/**
 * Specification:
 * - Removes an item from the recommendation engine.
 * - Updates the cache accordingly.
 */
class MyFacade {}
```

### With Custom Header

```php
/**
 * Requirements:
 *
 * - Removes an item from the recommendation engine.
 * - Updates the cache accordingly.
 */
class MyFacade {}
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
class MyFacade {}
```

### With Additional Description

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
 */
class MyFacade {}
```

### With Multi-Line List Items

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
class MyFacade {}
```

## Parameters

- `patterns`: Array of regex patterns to match against class names (with full namespace).
- `specificationHeader`: The header text to look for in the docblock (default: `'Specification:'`). You can use any custom text like `'Requirements:'`, `'Behavior:'`, etc.
- `requireBlankLineAfterHeader`: Whether a blank line is required after the header before the list items (default: `true`).
- `requireListItemsEndWithPeriod`: Whether all list items must end with a period to form proper sentences (default: `false`). This works correctly with multi-line list items.

## Note

List items can span multiple lines. Continuation lines are automatically recognized and joined with the main list item. When `requireListItemsEndWithPeriod` is enabled, the period is checked at the end of the complete list item (including all continuation lines).

