# Modular Architecture Rule

Enforces strict dependency rules for modular hexagonal (Ports and Adapters) architecture with capabilities/modules. This rule is specifically designed for modular monoliths where each capability/module follows a layered architecture pattern.

## What it enforces

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

## Architecture Structure

```
src/Capability/
  <ModuleName>/
    Domain/              # Pure business logic
    Application/         # Use cases, facades
    Infrastructure/      # Adapters (repositories, external services)
    Presentation/        # Controllers, CLI commands
```

## Configuration Example (Basic)

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

## Configuration Example (Custom Layers)

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

## Parameters

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

## Example Violations

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

## Custom Layer Examples

The rule is flexible and allows you to define your own architectural layers beyond the defaults. Here are some common use cases:

### 1. Stricter Isolation (Infrastructure cannot see Application)

```neon
layerDependencies:
    Domain: []
    Application: [Domain]
    Infrastructure: [Domain]  # Infrastructure isolated from Application
    Presentation: [Application]
```

### 2. Three-Tier Architecture (instead of hexagonal)

```neon
layerDependencies:
    Model: []                    # Data models
    Service: [Model]             # Business logic services
    Controller: [Service, Model] # Controllers/API
```

### 3. Onion Architecture with multiple layers

```neon
layerDependencies:
    Core: []                                    # Domain core
    DomainServices: [Core]                      # Domain services
    ApplicationServices: [DomainServices, Core] # Application services
    Infrastructure: [ApplicationServices, DomainServices, Core]
    Presentation: [ApplicationServices, Core]
```

### 4. Mixed layers (traditional + API-specific)

```neon
layerDependencies:
    Domain: []
    Application: [Domain]
    Infrastructure: [Domain, Application]
    Presentation: [Application, Domain]
    RestApi: [Application, Domain]      # Custom REST API layer
    GraphQLApi: [Application, Domain]   # Custom GraphQL API layer
```

## Custom Cross-Module Pattern Examples

Beyond the basic allowed imports (Facade, FacadeInterface, Input, Result), you can define custom patterns:

### 1. Allow DTOs and Contracts

```neon
allowedCrossModulePatterns:
    - '/Facade$/'
    - '/FacadeInterface$/'
    - '/Input$/'
    - '/Result$/'
    - '/Dto$/'              # Allow Data Transfer Objects
    - '/Contract$/'         # Allow Contract interfaces
```

### 2. Allow Query and Command objects (CQRS)

```neon
allowedCrossModulePatterns:
    - '/Facade$/'
    - '/FacadeInterface$/'
    - '/Query$/'            # Allow Query objects
    - '/Command$/'          # Allow Command objects
    - '/QueryResult$/'      # Allow Query results
```

### 3. Allow Events

```neon
allowedCrossModulePatterns:
    - '/Facade$/'
    - '/FacadeInterface$/'
    - '/Input$/'
    - '/Result$/'
    - '/Event$/'            # Allow Domain/Integration Events
    - '/EventInterface$/'   # Allow Event interfaces
```

### 4. Custom patterns with namespace matching

```neon
allowedCrossModulePatterns:
    - '/Facade$/'
    - '/FacadeInterface$/'
    - '/^.*Contract$/'      # Any class starting with anything and ending with "Contract"
    - '/^I[A-Z]/'           # Any interface starting with "I" (e.g., IUserService)
```

## Pattern Matching Examples

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

