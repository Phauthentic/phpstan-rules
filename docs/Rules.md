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

## Modular Architecture Rule

Enforces strict dependency rules for modular hexagonal (Ports and Adapters) architecture with capabilities/modules. This rule is specifically designed for modular monoliths where each capability/module follows a layered architecture pattern.

**What it enforces:**

1. **Intra-Module Layer Dependencies** - Within the same module (default Clean Architecture configuration):
   - **Domain**: Cannot import from any other layer (pure business logic)
   - **Application**: Can import Domain only (defines use cases and port interfaces)
   - **Infrastructure**: Can import Domain and Application (implements port interfaces defined in Application)
   - **Presentation**: Can import Application only (calls use cases)
   - **All layers can import from themselves** (e.g., Presentation → Presentation within the same layer)
   
   This follows the **Dependency Inversion Principle**: Application defines interfaces, Infrastructure implements them.
   
   **Note:** You can customize these layer dependencies to match your architecture needs (see configuration examples below).

2. **Cross-Module Dependencies** - Between different modules:
   - You must explicitly configure which classes can be imported cross-module using regex patterns
   - Common patterns include:
     - `*Facade.php` and `*FacadeInterface.php`
     - `*Input.php` (DTOs from UseCases)
     - `*Result.php` (DTOs from UseCases)
   - Without configured patterns, ALL cross-module imports are forbidden
   
   **Note:** There are no default cross-module patterns - you must explicitly configure them based on your architecture needs.

**Note:** For circular dependency detection between modules, use the separate `CircularModuleDependencyRule`.

**Architecture Structure:**

```
src/Capability/
  <ModuleName>/
    Domain/              # Pure business logic
    Application/         # Use cases, facades
    Infrastructure/      # Adapters (repositories, external services)
    Presentation/        # Controllers, CLI commands
```

**Configuration Example (Basic):**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ModularArchitectureRule
        arguments:
            baseNamespace: 'App\Capability'
            layerDependencies: null  # Uses default Clean Architecture rules
            allowedCrossModulePatterns:
                - '/Facade$/'           # Classes ending with "Facade"
                - '/FacadeInterface$/'  # Classes ending with "FacadeInterface"
                - '/Input$/'            # Classes ending with "Input"
                - '/Result$/'           # Classes ending with "Result"
        tags:
            - phpstan.rules.rule
```

**Configuration Example (Custom Layers):**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ModularArchitectureRule
        arguments:
            baseNamespace: 'App\Capability'
            layerDependencies:
                Domain: []                           # Domain cannot depend on anything
                Application: [Domain, Infrastructure] # Custom: Application can depend on Infrastructure
                Infrastructure: [Domain]
                Presentation: [Application, Domain]
                # You can also define your own custom layers:
                Api: [Application, Domain]
                Cli: [Application, Domain]
        tags:
            - phpstan.rules.rule
```

**Configuration Example (Custom Cross-Module Patterns):**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ModularArchitectureRule
        arguments:
            baseNamespace: 'App\Capability'
            layerDependencies: null  # Use defaults
            allowedCrossModulePatterns:
                - '/Facade$/'           # Classes ending with "Facade"
                - '/FacadeInterface$/'  # Classes ending with "FacadeInterface"
                - '/Input$/'            # Classes ending with "Input"
                - '/Result$/'           # Classes ending with "Result"
                - '/Dto$/'              # Custom: Allow Data Transfer Objects
                - '/^.*Contract$/'      # Custom: Allow Contract interfaces
                - '/Query$/'            # Custom: Allow Query objects
        tags:
            - phpstan.rules.rule
```

**Parameters:**

- `baseNamespace`: The base namespace for your capabilities/modules (e.g., `App\Capability`)
- `layerDependencies`: (Optional) Custom layer dependency rules. If not provided, uses default Clean Architecture rules.
  - Format: `LayerName: [AllowedDependency1, AllowedDependency2, ...]`
  - Default layers (following Dependency Inversion Principle):
    - `Domain: []` - Pure business logic
    - `Application: [Domain]` - Use cases and port interfaces
    - `Infrastructure: [Domain, Application]` - Implements Application interfaces
    - `Presentation: [Application]` - Controllers, CLI commands
  - You can define any custom layer names you need
- `allowedCrossModulePatterns`: **Required** - Regex patterns for fully qualified class names that can be imported across modules.
  - **No defaults** - you must explicitly configure which classes can cross module boundaries
  - Patterns match against the **fully qualified class name** (e.g., `App\Capability\User\UserFacade`)
  - Common patterns:
    - `/Facade$/` - Classes ending with "Facade"
    - `/FacadeInterface$/` - Classes ending with "FacadeInterface"
    - `/Input$/` - Classes ending with "Input"
    - `/Result$/` - Classes ending with "Result"
    - `/^App\\Capability\\.*\\Application\\Queries\\.*$/` - All classes in Application\Queries namespace
  - Empty array `[]` = no cross-module imports allowed (complete module isolation)

**Example Violations:**

```php
// ❌ Domain importing from Application
namespace App\Capability\UserManagement\Domain;
use App\Capability\UserManagement\Application\CreateUser;

// ❌ Application importing from Presentation
namespace App\Capability\UserManagement\Application;
use App\Capability\UserManagement\Presentation\UserController;

// ❌ Cross-module exception import
namespace App\Capability\ProductCatalog\Application;
use App\Capability\UserManagement\UserManagementException;

// ✅ Valid cross-module facade import
namespace App\Capability\ProductCatalog\Application;
use App\Capability\UserManagement\UserManagementFacade;
use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserInput;

// ✅ Valid layer dependency
namespace App\Capability\UserManagement\Application;
use App\Capability\UserManagement\Domain\Model\User;
```

**Custom Layer Examples:**

The rule is flexible and allows you to define your own architectural layers beyond the defaults. Here are some common use cases:

1. **Stricter Isolation** (Infrastructure cannot see Application):
```neon
layerDependencies:
    Domain: []
    Application: [Domain]
    Infrastructure: [Domain]  # Infrastructure isolated from Application
    Presentation: [Application]
```

2. **Three-Tier Architecture** (instead of hexagonal):
```neon
layerDependencies:
    Model: []                    # Data models
    Service: [Model]             # Business logic services
    Controller: [Service, Model] # Controllers/API
```

3. **Onion Architecture** with multiple layers:
```neon
layerDependencies:
    Core: []                                    # Domain core
    DomainServices: [Core]                      # Domain services
    ApplicationServices: [DomainServices, Core] # Application services
    Infrastructure: [ApplicationServices, DomainServices, Core]
    Presentation: [ApplicationServices, Core]
```

4. **Mixed layers** (traditional + API-specific):
```neon
layerDependencies:
    Domain: []
    Application: [Domain]
    Infrastructure: [Domain, Application]
    Presentation: [Application, Domain]
    RestApi: [Application, Domain]      # Custom REST API layer
    GraphQLApi: [Application, Domain]   # Custom GraphQL API layer
```

**Custom Cross-Module Pattern Examples:**

Beyond the default allowed imports (Facade, FacadeInterface, Input, Result), you can define custom patterns:

1. **Allow DTOs and Contracts**:
```neon
allowedCrossModulePatterns:
    - '/Facade$/'
    - '/FacadeInterface$/'
    - '/Input$/'
    - '/Result$/'
    - '/Dto$/'              # Allow Data Transfer Objects
    - '/Contract$/'         # Allow Contract interfaces
```

2. **Allow Query and Command objects** (CQRS):
```neon
allowedCrossModulePatterns:
    - '/Facade$/'
    - '/FacadeInterface$/'
    - '/Query$/'            # Allow Query objects
    - '/Command$/'          # Allow Command objects
    - '/QueryResult$/'      # Allow Query results
```

3. **Allow Events**:
```neon
allowedCrossModulePatterns:
    - '/Facade$/'
    - '/FacadeInterface$/'
    - '/Input$/'
    - '/Result$/'
    - '/Event$/'            # Allow Domain/Integration Events
    - '/EventInterface$/'   # Allow Event interfaces
```

4. **Custom patterns with namespace matching**:
```neon
allowedCrossModulePatterns:
    - '/Facade$/'
    - '/FacadeInterface$/'
    - '/^.*Contract$/'      # Any class starting with anything and ending with "Contract"
    - '/^I[A-Z]/'           # Any interface starting with "I" (e.g., IUserService)
```

**Pattern Matching Examples:**

Patterns match against the **fully qualified class name**:

```php
// Class: App\Capability\UserManagement\UserManagementFacade
'/Facade$/'                                    // ✅ Matches (ends with Facade)
'/^App\\Capability\\.*\\Facade$/'             // ✅ Matches (full namespace pattern)

// Class: App\Capability\User\Application\Queries\FindUser\FindUserQuery
'/Query$/'                                     // ✅ Matches (ends with Query)
'/^App\\Capability\\.*\\Application\\Queries\\.*$/'  // ✅ Matches (namespace pattern)
'/^App\\Capability\\User\\.*$/'               // ✅ Matches (specific module)
```

This allows you to be very specific about which classes can cross module boundaries based on their location in the namespace hierarchy.

## Circular Module Dependency Rule

Detects circular dependencies between modules in a modular architecture. This rule tracks module-to-module dependencies and reports when circular dependencies are detected.

**What it enforces:**

- Tracks dependencies between modules across the entire codebase
- Detects circular dependency chains: Module A → Module B → Module C → Module A
- Reports the complete dependency cycle path

**Architecture Structure:**

```
src/Capability/
  ModuleA/
  ModuleB/
  ModuleC/
```

If ModuleA imports from ModuleB, ModuleB imports from ModuleC, and ModuleC imports back to ModuleA, a circular dependency is detected.

**Configuration Example:**

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\CircularModuleDependencyRule
        arguments:
            baseNamespace: 'App\Capability'
        tags:
            - phpstan.rules.rule
```

**Parameters:**

- `baseNamespace`: The base namespace for your capabilities/modules (e.g., `App\Capability`)

**Example Violations:**

```php
// ❌ Circular dependency detected
// In ProductCatalog module:
use App\Capability\Billing\BillingFacade;

// In Billing module:
use App\Capability\UserManagement\UserManagementFacade;

// In UserManagement module:
use App\Capability\ProductCatalog\ProductCatalogFacade;

// Results in: ProductCatalog → Billing → UserManagement → ProductCatalog
```

**Note:** This rule should be used together with `ModularArchitectureRule` for comprehensive architectural enforcement. The `ModularArchitectureRule` handles layer and cross-module dependencies, while this rule specifically handles circular dependency detection.

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
