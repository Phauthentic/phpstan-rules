# PHPStan Rules

Additional rules for PHPStan. Mostly focused on clean code and architecture.

## Usage

```bash
composer require/phauthentic/phpstan-rules --dev
```

## Rules

Add them to your `phpstan.neon` configuration file under the section `services`.

### Control Structure Nesting Rule

Ensures that the nesting level of `if` and `try-catch` statements does not exceed a specified limit.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\ControlStructureNestingRule
        arguments:
            maxNestingLevel: 2
        tags:
            - phpstan.rules.rule
```

### Too Many Arguments Rule

Checks that methods do not have more than a specified number of arguments.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\TooManyArgumentsRule
        arguments:
            maxArguments: 3
        tags:
            - phpstan.rules.rule
```
### Readonly Class Rule

Ensures that classes matching specified patterns are declared as `readonly`.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\ReadonlyClassRule
        arguments:
            patterns: ['/^App\\Controller\\/']
        tags:
            - phpstan.rules.rule
```

### Dependency Constraints Rule

Enforces dependency constraints between namespaces by checking `use` statements.

The constructor takes an array of namespace dependencies. The key is the namespace that should not depend on the namespaces in the array of values.

In the example below nothing from `App\Domain` can depend on anything from `App\Controller`.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\DependencyConstraintsRule
        arguments:
            namespaceDependencies: [
                'App\\Domain\\': ['/^App\\Controller\\/']
            ]
        tags:
            - phpstan.rules.rule
```

### Final Class Rule

Ensures that classes matching specified patterns are declared as `final`.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\FinalClassRule
        arguments:
            patterns: ['/^App\\Service\\/']
        tags:
            - phpstan.rules.rule
```

### Namespace Class Pattern Rule

Ensures that classes inside namespaces matching a given regex must have names matching at least one of the provided patterns.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\NamespaceClassPatternRule
        arguments:
            namespaceClassPatterns: [
                [
                    namespace: '/^App\\Service$/',
                    classPatterns: ['/Class$/']
                ]
            ]
        tags:
            - phpstan.rules.rule
```

## License

This library is under the MIT license.

Copyright Florian Krämer
