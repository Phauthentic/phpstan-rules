# Forbidden Static Methods Rule

Forbids specific static method calls matching regex patterns. This rule checks static method calls against a configurable list of forbidden patterns and supports namespace-level, class-level, and method-level granularity.

The rule resolves `self`, `static`, and `parent` keywords to the actual class name before matching, so forbidden patterns work correctly even when these keywords are used.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenStaticMethodsRule
        arguments:
            forbiddenStaticMethods:
                - '/^App\\Legacy\\.*::.*/'
                - '/^App\\Utils\\StaticHelper::.*/'
                - '/^DateTime::createFromFormat$/'
        tags:
            - phpstan.rules.rule
```

## Parameters

- `forbiddenStaticMethods`: Array of regex patterns to match against static method calls. Patterns are matched against the format `FQCN::methodName`.

## Pattern Granularity

Patterns match against the fully qualified class name followed by `::` and the method name. This allows you to forbid static calls at different levels of granularity:

### Namespace-level

Forbid all static calls to any class in a namespace:

```neon
    forbiddenStaticMethods:
        - '/^App\\Legacy\\.*::.*/'
```

This forbids calls like `App\Legacy\LegacyHelper::doSomething()` and `App\Legacy\OldService::run()`.

### Class-level

Forbid all static calls on a specific class:

```neon
    forbiddenStaticMethods:
        - '/^App\\Utils\\StaticHelper::.*/'
```

This forbids all static method calls on `App\Utils\StaticHelper`, regardless of the method name.

### Method-level

Forbid a specific static method on a specific class:

```neon
    forbiddenStaticMethods:
        - '/^DateTime::createFromFormat$/'
```

This forbids only `DateTime::createFromFormat()` while allowing other static methods like `DateTime::getLastErrors()`.

## Use Cases

### Forbid Legacy Static Helpers

Prevent usage of legacy static helper classes to encourage dependency injection:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenStaticMethodsRule
        arguments:
            forbiddenStaticMethods:
                - '/^App\\Legacy\\.*::.*/'
                - '/^App\\Helpers\\.*::.*/'
        tags:
            - phpstan.rules.rule
```

### Forbid Specific Factory Methods

Forbid using static factory methods on certain classes while allowing other static methods:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenStaticMethodsRule
        arguments:
            forbiddenStaticMethods:
                - '/^DateTime::createFromFormat$/'
                - '/^DateTime::createFromTimestamp$/'
        tags:
            - phpstan.rules.rule
```

### Forbid All Static Calls in Domain Layer

Combine with a broad pattern to forbid all static calls from specific namespaces:

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenStaticMethodsRule
        arguments:
            forbiddenStaticMethods:
                - '/^Illuminate\\Support\\Facades\\.*::.*/'
        tags:
            - phpstan.rules.rule
```

## Handling of self, static, and parent

The rule resolves the keywords `self`, `static`, and `parent` to the actual fully qualified class name before matching against the forbidden patterns. This means:

- `self::create()` inside `App\Service\MyService` is matched as `App\Service\MyService::create`
- `static::create()` inside `App\Service\MyService` is matched as `App\Service\MyService::create`
- `parent::create()` inside a child class is matched against the parent class name

Dynamic class names (e.g., `$class::method()`) and dynamic method names (e.g., `DateTime::$method()`) are skipped.
