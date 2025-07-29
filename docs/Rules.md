# Rules

Add them to your `phpstan.neon` configuration file under the section `services`.

## Control Structure Nesting Rule {#control-structure-nesting-rule}

Ensures that the nesting level of `if` and `try-catch` statements does not exceed a specified limit.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\ControlStructureNestingRule
        arguments:
            maxNestingLevel: 2
        tags:
            - phpstan.rules.rule
```

## Too Many Arguments Rule {#too-many-arguments-rule}

Checks that methods do not have more than a specified number of arguments.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\TooManyArgumentsRule
        arguments:
            maxArguments: 3
        tags:
            - phpstan.rules.rule
```

## Readonly Class Rule {#readonly-class-rule}

Ensures that classes matching specified patterns are declared as `readonly`.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustBeReadonlyRule
        arguments:
            patterns: ['/^App\\Controller\\/']
        tags:
            - phpstan.rules.rule
```

## Dependency Constraints Rule {#dependency-constraints-rule}

Enforces dependency constraints between namespaces by checking `use` statements.

The constructor takes an array of namespace dependencies. The key is the namespace that should not depend on the namespaces in the array of values.

In the example below nothing from `App\Domain` can depend on anything from `App\Controller`.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\DependencyConstraintsRule
        arguments:
            forbiddenDependencies: [
                '/^App\\Domain(?:\\\w+)*$/': ['/^App\\Controller\\/']
            ]
        tags:
            - phpstan.rules.rule
```

## Final Class Rule {#final-class-rule}

Ensures that classes matching specified patterns are declared as `final`.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustBeFinalRule
        arguments:
            patterns: ['/^App\\Service\\/']
        tags:
            - phpstan.rules.rule
```

## Namespace Class Pattern Rule {#namespace-class-pattern-rule}

Ensures that classes inside namespaces matching a given regex must have names matching at least one of the provided patterns.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassnameMustMatchPatternRule
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

## Catch Exception of Type Not Allowed Rule {#catch-exception-of-type-not-allowed-rule}

Ensures that specific exception types are not caught in catch blocks. This is useful for preventing the catching of overly broad exception types like `Exception`, `Error`, or `Throwable`.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\CatchExceptionOfTypeNotAllowedRule
        arguments:
            forbiddenExceptionTypes: ['Exception', 'Error', 'Throwable']
        tags:
            - phpstan.rules.rule
```

## Methods Returning Bool Must Follow Naming Convention Rule {#methods-returning-bool-must-follow-naming-convention-rule}

Ensures that methods returning boolean values follow a specific naming convention. By default, boolean methods should start with `is`, `has`, `can`, `should`, `was`, or `will`.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\MethodsReturningBoolMustFollowNamingConventionRule
        arguments:
            regex: '/^(is|has|can|should|was|will)[A-Z_]/'
        tags:
            - phpstan.rules.rule
```

## Method Signature Must Match Rule {#method-signature-must-match-rule}

Ensures that methods matching a class and method name pattern have a specific signature, including parameter types, names, and count.

**Configuration Example:**

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

- `pattern`: Regex for `ClassName::methodName`.
- `minParameters`/`maxParameters`: Minimum/maximum number of parameters.
- `signature`: List of expected parameter types and (optionally) name patterns.
- `visibilityScope`: Optional visibility scope (e.g., `public`, `protected`, `private`).

## Method Must Return Type Rule {#method-must-return-type-rule}

Ensures that methods matching a class and method name pattern have a specific return type, nullability, or are void. Supports union types with "oneOf" and "allOf" configurations.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule
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
                    objectTypePattern: '/^App\\Entity\\User$/'
                -
                    pattern: '/^MyClass::reset$/'
                    type: 'void'
                    nullable: false
                    void: false
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::getValue$/'
                    nullable: false
                    void: false
                    oneOf: ['int', 'string', 'bool']
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::getUnionType$/'
                    nullable: false
                    void: false
                    allOf: ['int', 'string']
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::getAnyType$/'
                    anyOf: ['object', 'void']
                    objectTypePattern: null
        tags:
            - phpstan.rules.rule
```

- `pattern`: Regex for `ClassName::methodName`.
- `type`: Expected return type (`int`, `string`, `object`, `void`, etc.). When using `oneOf` or `allOf`, this field is optional.
- `nullable`: Whether the return type must be nullable.
- `void`: Legacy field for void return types (use `type: 'void'` instead).
- `objectTypePattern`: Regex for object return types (if `type` is `object`).
- `oneOf`: Array of types where one must match (for union types).
- `allOf`: Array of types where all must be present in the union type.
- `anyOf`: Alias for `oneOf` - array of types where one must match.
