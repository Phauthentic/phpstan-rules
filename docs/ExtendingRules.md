# Extending Rules

This package provides flexible base rules that can be extended in your project to create domain-specific, self-documenting rules. Instead of configuring complex regex patterns directly in your `phpstan.neon` file, you can create custom rule classes that encapsulate the configuration.

## Benefits of Extending Rules

- **Self-Documenting Code**: A class named `ImmutableDateTimeIsForbiddenInModulesRule` immediately communicates its purpose, unlike a generic configuration block.
- **Reusability**: The rule can be shared across multiple projects or teams without copying configuration.
- **IDE Support**: Full autocompletion and refactoring support in your IDE.
- **Testability**: Custom rules can be unit tested independently.
- **Single Responsibility**: Each rule class has one clear purpose, making maintenance easier.
- **Cleaner Configuration**: Your `phpstan.neon` stays minimal and readable.

## Examples

### Example 1: Forbid DateTime in Module Namespaces

This rule forbids the usage of PHP's mutable `DateTime` class in your module namespaces, encouraging the use of `DateTimeImmutable` or domain-specific value objects.

**Create the rule class:**

```php
<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use Phauthentic\PHPStanRules\Architecture\ForbiddenDependenciesRule;

/**
 * Prevents usage of mutable DateTime in module namespaces.
 * Use DateTimeImmutable or domain-specific date value objects instead.
 */
class ImmutableDateTimeIsForbiddenInModulesRule extends ForbiddenDependenciesRule
{
    public function __construct()
    {
        parent::__construct(
            forbiddenDependencies: [
                '/^App\\\\Module\\\\.*/' => [
                    '/^DateTime$/'
                ]
            ],
            checkFqcn: true
        );
    }
}
```

**Register in `phpstan.neon`:**

```neon
services:
    -
        class: App\PHPStan\Rules\ImmutableDateTimeIsForbiddenInModulesRule
        tags:
            - phpstan.rules.rule
```

### Example 2: Domain Classes Must Be Final

This rule ensures all classes in your domain layer are declared as `final` to prevent inheritance and encourage composition over inheritance.

**Create the rule class:**

```php
<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use Phauthentic\PHPStanRules\Architecture\ClassMustBeFinalRule;

/**
 * Enforces that all domain classes are final.
 * This promotes composition over inheritance in the domain layer.
 */
class DomainClassesMustBeFinalRule extends ClassMustBeFinalRule
{
    public function __construct()
    {
        parent::__construct(
            patterns: [
                '/^App\\\\Domain\\\\.*/'
            ],
            ignoreAbstractClasses: true
        );
    }
}
```

**Register in `phpstan.neon`:**

```neon
services:
    -
        class: App\PHPStan\Rules\DomainClassesMustBeFinalRule
        tags:
            - phpstan.rules.rule
```

### Example 3: Forbid Legacy Namespace Creation

This rule prevents developers from creating classes in deprecated or legacy namespaces, helping to enforce architectural boundaries during refactoring efforts.

**Create the rule class:**

```php
<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use Phauthentic\PHPStanRules\Architecture\ForbiddenNamespacesRule;

/**
 * Prevents creation of classes in legacy namespaces.
 * All new code should use the App\Module\* namespace structure.
 */
class LegacyNamespaceForbiddenRule extends ForbiddenNamespacesRule
{
    public function __construct()
    {
        parent::__construct(
            forbiddenNamespaces: [
                '/^App\\\\Legacy\\\\.*/',
                '/^App\\\\Old\\\\.*/',
                '/^App\\\\Deprecated\\\\.*/'
            ]
        );
    }
}
```

**Register in `phpstan.neon`:**

```neon
services:
    -
        class: App\PHPStan\Rules\LegacyNamespaceForbiddenRule
        tags:
            - phpstan.rules.rule
```

## Complete Configuration Example

Here's how your `phpstan.neon` might look with multiple custom rules:

```neon
parameters:
    level: 8
    paths:
        - src

services:
    -
        class: App\PHPStan\Rules\ImmutableDateTimeIsForbiddenInModulesRule
        tags:
            - phpstan.rules.rule

    -
        class: App\PHPStan\Rules\DomainClassesMustBeFinalRule
        tags:
            - phpstan.rules.rule

    -
        class: App\PHPStan\Rules\LegacyNamespaceForbiddenRule
        tags:
            - phpstan.rules.rule
```

## Customizing Error Messages and Identifiers

If you need custom error messages, you can override the protected constants:

```php
<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use Phauthentic\PHPStanRules\Architecture\ForbiddenDependenciesRule;

class ImmutableDateTimeIsForbiddenInModulesRule extends ForbiddenDependenciesRule
{
    protected const ERROR_MESSAGE = 'Mutable DateTime is not allowed in modules. Use DateTimeImmutable instead. Found dependency from `%s` to `%s`.';

    protected const IDENTIFIER = 'app.architecture.immutableDateTimeInModules';

    public function __construct()
    {
        parent::__construct(
            forbiddenDependencies: [
                '/^App\\\\Module\\\\.*/' => [
                    '/^DateTime$/'
                ]
            ],
            checkFqcn: true
        );
    }
}
```

## Best Practices

1. **Use descriptive class names** that clearly communicate the rule's purpose.
2. **Add PHPDoc comments** explaining why the rule exists and what alternatives developers should use.
3. **Place rules in a dedicated namespace** like `App\PHPStan\Rules` or `App\Infrastructure\PHPStan`.
4. **Consider creating a base rule class** for your project if you have common patterns across multiple rules.
5. **Write tests** for your custom rules to ensure they catch the intended violations.
