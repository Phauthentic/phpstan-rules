# Rules

Add them to your `phpstan.neon` configuration file under the section `services`.

## Control Structure Nesting Rule

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

## Too Many Arguments Rule

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

## Max Line Length Rule

Checks that lines do not exceed a specified maximum length. Provides options to exclude files by pattern and ignore use statements.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule
        arguments:
            maxLineLength: 80
            excludePatterns: ['/.*\.generated\.php$/', '/.*vendor.*/']
            ignoreUseStatements: false
        tags:
            - phpstan.rules.rule
```

- `maxLineLength`: Maximum allowed line length in characters (default: 80).
- `excludePatterns`: Array of regex patterns to exclude files from checking (optional).
- `ignoreUseStatements`: Whether to ignore use statement lines (default: false).

## Readonly Class Rule

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

## Dependency Constraints Rule

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

## Forbidden Namespaces Rule

Enforces that certain namespaces cannot be declared in your codebase. This rule checks the `namespace` keyword and prevents the declaration of namespaces matching specified regex patterns, helping to enforce architectural constraints.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenNamespacesRule
        arguments:
            forbiddenNamespaces: [
                '/^App\\Legacy\\.*/',
                '/^App\\Deprecated\\.*/'
            ]
        tags:
            - phpstan.rules.rule
```

- `forbiddenNamespaces`: Array of regex patterns matching namespaces that are not allowed to be declared.

## Final Class Rule

Ensures that classes matching specified patterns are declared as `final`.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustBeFinalRule
        arguments:
            patterns: ['/^App\\Service\\/']
            ignoreAbstractClasses: true
        tags:
            - phpstan.rules.rule
```

- `patterns`: Array of regex patterns to match against class names.
- `ignoreAbstractClasses`: Whether to ignore abstract classes (default: `true`).

## Namespace Class Pattern Rule

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

## Catch Exception of Type Not Allowed Rule

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

## Methods Returning Bool Must Follow Naming Convention Rule

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

## Method Signature Must Match Rule

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

## Method Must Return Type Rule

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
                -
                    pattern: '/^MyClass::getEntity$/'
                    anyOf: ['regex:/^App\\Entity\\/', 'void']
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

**Regex Support**: You can use regex patterns in `oneOf`, `allOf`, and `anyOf` arrays by prefixing them with `regex:`. For example:
- `'regex:/^App\\Entity\\/'` - matches any class starting with "App\Entity\"
- `'regex:/^UserEntity$/'` - matches exactly "UserEntity"

## Example

Here is a full example for a modular monolith with clean architecture rules.

```yaml
services:
    # Architecture Rules

    # Enforce final classes for controllers
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustBeFinalRule
        arguments:
            patterns:
                - '/^App\\Capability\\.*\\Presentation\\Http\\Controller/'
                - '/^App\\Capability\\.*\\Application\\UseCases/'
                - '/^App\\Capability\\.*\\Application\\Jobs/'
                - '/^App\\Capability\\.*\\Application\\IntegrationEventHandler/'
                - '/^App\\Capability\\.*\\Application\\DomainEventHandler/'
                - '/^App\\Capability\\.*\\Application\\.*Facade$/'

        tags:
            - phpstan.rules.rule

    # Enforce readonly classes for DTOs and value objects
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassMustBeReadonlyRule
        arguments:
            patterns:
                - '/^App\\Capability\\.*\\Application\\Transfers/'
                - '/^App\\Capability\\.*\\Application\\UseCases\\.*Input$/'
                - '/^App\\Capability\\.*\\Application\\UseCases\\.*Result$/'
                - '/^App\\Capability\\.*\\Application\\Query\\.*Input$/'
                - '/^App\\Capability\\.*\\Application\\Query\\.*Result$/'
                - '/^App\\Capability\\.*\\Domain\\Model\\.*\\ValueObject/'
                - '/^App\\Capability\\.*\\Application\\.*Facade$/'
        tags:
            - phpstan.rules.rule

    # Dependency constraints - enforce layer boundaries
    -
        class: Phauthentic\PHPStanRules\Architecture\DependencyConstraintsRule
        arguments:
            forbiddenDependencies:
                # Domain layer cannot depend on Application, Infrastructure, or Presentation
                '/^App\\Capability\\.*\\Domain/':
                    - '/^App\\Capability\\.*\\Application/'
                    - '/^App\\Capability\\.*\\Infrastructure/'
                    - '/^App\\Capability\\.*\\Presentation/'
                # Application layer cannot depend on Infrastructure or Presentation
                '/^App\\Capability\\.*\\Application/':
                    - '/^App\\Capability\\.*\\Infrastructure/'
                    - '/^App\\Capability\\.*\\Presentation/'
                # Infrastructure layer cannot depend on Presentation
                '/^App\\Capability\\.*\\Infrastructure/':
                    - '/^App\\Capability\\.*\\Presentation/'
        tags:
            - phpstan.rules.rule

    # Class naming patterns for different layers
    -
        class: Phauthentic\PHPStanRules\Architecture\ClassnameMustMatchPatternRule
        arguments:
            namespaceClassPatterns:
                # Facades must end with "Facade"
                -
                    namespace: '/^App\\Capability\\.*\\Application$/'
                    classPatterns:
                        - '/Facade$/'
                        - '/FacadeInterface$/'
                        - '/Exception$/'
                        - '/Config/'
                # Controllers must end with "Controller"
                -
                    namespace: '/^App\\Capability\\.*\\Presentation\\.*\\Controller/'
                    classPatterns:
                        - '/Controller$/'
                # Repositories must end with "Repository" or "RepositoryInterface"
                -
                    namespace: '/^App\\Capability\\.*\\Domain\\Repository/'
                    classPatterns:
                        - '/RepositoryInterface$/'
                -
                    namespace: '/^App\\Capability\\.*\\Infrastructure\\Persistence\\.*\\Repository/'
                    classPatterns:
                        - '/Repository$/'
                # Aggregate IDs must end with "Id"
                -
                    namespace: '/^App\\Capability\\.*\\Domain\\Model\\.*$/'
                    classPatterns:
                        - '/Id$/'
                # Transfer objects must end with "Request" or "Result"
                -
                    namespace: '/^App\\Capability\\.*\\Application\\Transfers/'
                    classPatterns:
                        - '/Request$/'
                        - '/Result$/'
                # Use case inputs and results
                -
                    namespace: '/^App\\Capability\\.*\\Application\\UseCases/'
                    classPatterns:
                        - '/Input$/'
                        - '/Result$/'
                        - '/^[A-Z][a-zA-Z0-9]*$/'  # Use case classes
                # Query inputs and results
                -
                    namespace: '/^App\\Capability\\.*\\Application\\Query/'
                    classPatterns:
                        - '/Input$/'
                        - '/Result$/'
                        - '/^[A-Z][a-zA-Z0-9]*$/'  # Query classes
        tags:
            - phpstan.rules.rule

    # Method signature rules for repositories
    -
        class: Phauthentic\PHPStanRules\Architecture\MethodSignatureMustMatchRule
        arguments:
            signaturePatterns:
                # Repository persist method
                -
                    pattern: '/Repository::persist$/'
                    minParameters: 1
                    maxParameters: 1
                    signature:
                        -
                            pattern: '/^[a-zA-Z][a-zA-Z0-9]*$/'
                # Repository get method
                -
                    pattern: '/Repository::get$/'
                    minParameters: 1
                    maxParameters: 1
        tags:
            - phpstan.rules.rule

    # Method return type rules
    -
        class: Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule
        arguments:
            returnTypePatterns:
                # Repository get method must return object or null
                -
                    pattern: '/Repository::get$/'
                    type: 'object'
                    nullable: true
                    void: false
                    objectTypePattern: null
                # Repository persist method must return void
                -
                    pattern: '/Repository::persist$/'
                    type: 'void'
                    nullable: false
                    void: true
                    objectTypePattern: null
                # Facade methods must return Result objects
                -
                    pattern: '/Facade::[a-zA-Z]+$/'
                    anyOf:
                        - 'regex:/^App\\Capability\\.*\\Application\\.*\\[a-zA-Z]+Result$/'
                        - void
        tags:
            - phpstan.rules.rule

    # Forbidden namespaces
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenNamespacesRule
        arguments:
            forbiddenNamespaces: [
            ## Matches everything that is NOT in PublicAPI or InternalAPI, modify this only if you want to allow
            ## new code in other types of presentations!
                '/^App\\Capability\\[^\\]+\\Presentation\\(?!Http$|PublicAPI$|InternalAPI$)[^\\]+$/'
            ]
        tags:
            - phpstan.rules.rule

    # Clean Code Rules

    # Control structure nesting
    -
        class: Phauthentic\PHPStanRules\CleanCode\ControlStructureNestingRule
        arguments:
            maxNestingLevel: 3
        tags:
            - phpstan.rules.rule
```
