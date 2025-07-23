# Rules

Add them to your `phpstan.neon` configuration file under the section `services`.

## Control Structure Nesting Rule

Ensures that the nesting level of `if` and `try-catch` statements does not exceed a specified limit.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\CleanCode\ControlStructureNestingRule
        arguments:
            maxNestingLevel: 2
        tags:
            - phpstan.rules.rule
```

## Too Many Arguments Rule

Checks that methods do not have more than a specified number of arguments.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\CleanCode\TooManyArgumentsRule
        arguments:
            maxArguments: 3
        tags:
            - phpstan.rules.rule
```

## Readonly Class Rule

Ensures that classes matching specified patterns are declared as `readonly`.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\Architecture\ReadonlyClassRule
        arguments:
            patterns: ['/^App\\Controller\\/']
        tags:
            - phpstan.rules.rule
```

## Dependency Constraints Rule

Enforces dependency constraints between namespaces by checking `use` statements.

The constructor takes an array of namespace dependencies. The key is the namespace that should not depend on the namespaces in the array of values.

In the example below nothing from `App\Domain` can depend on anything from `App\Controller`.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\Architecture\DependencyConstraintsRule
        arguments:
            forbiddenDependencies: [
                '/^App\\Domain(?:\\\w+)*$/': ['/^App\\Controller\\/']
            ]
        tags:
            - phpstan.rules.rule
```

## Final Class Rule

Ensures that classes matching specified patterns are declared as `final`.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\Architecture\FinalClassRule
        arguments:
            patterns: ['/^App\\Service\\/']
        tags:
            - phpstan.rules.rule
```

## Namespace Class Pattern Rule

Ensures that classes inside namespaces matching a given regex must have names matching at least one of the provided patterns.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\Architecture\NamespaceClassPatternRule
        arguments:
            namespaceClassPatterns: [
                [
                    namespace: '/^App\\Service$/',
                    classPatterns: [
                        '/Class$/'
                    ]
                ]
            ]
        tags:
            - phpstan.rules.rule
```

## Catch Exception of Type Not Allowed Rule

Ensures that specific exception types are not caught in catch blocks. This is useful for preventing the catching of overly broad exception types like `Exception`, `Error`, or `Throwable`.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\Architecture\CatchExceptionOfTypeNotAllowedRule
        arguments:
            forbiddenExceptionTypes: ['Exception', 'Error', 'Throwable']
        tags:
            - phpstan.rules.rule
```

## Method Signature Must Match Rule

Ensures that methods matching a class and method name pattern have a specific signature, including parameter types, names, and count.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\Architecture\MethodSignatureMustMatchRule
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
- `pattern`: Regex for `ClassName::methodName`.
- `minParameters`/`maxParameters`: Minimum/maximum number of parameters.
- `signature`: List of expected parameter types and (optionally) name patterns.

## Method Must Return Type Rule

Ensures that methods matching a class and method name pattern have a specific return type, nullability, or are void.

**Configuration Example:**
```neon
    -
        class: Phauthentic\PhpstanRules\Architecture\MethodMustReturnTypeRule
        arguments:
            returnTypePatterns:
                -
                    pattern: '/^MyClass::getId$/'
                    type: 'int'
                    nullable: false
                    void: false
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::findUser$/'
                    type: 'object'
                    nullable: true
                    void: false
                    objectTypePattern: '/^App\\\\Entity\\\\User$/'
                -
                    pattern: '/^MyClass::reset$/'
                    type: 'void'
                    nullable: false
                    void: true
                    objectTypePattern: null
        tags:
            - phpstan.rules.rule
```
- `pattern`: Regex for `ClassName::methodName`.
- `type`: Expected return type (`int`, `string`, `object`, etc.).
- `nullable`: Whether the return type must be nullable.
- `void`: Whether the method must return void.
- `objectTypePattern`: Regex for object return types (if `type` is `object`).
