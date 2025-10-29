# Circular Module Dependency Rule

Detects circular dependencies between modules in a modular architecture. This rule tracks module-to-module dependencies and reports when circular dependencies are detected.

## What it enforces

- Tracks dependencies between modules across the entire codebase
- Detects circular dependency chains: Module A → Module B → Module C → Module A
- Reports the complete dependency cycle path

## Architecture Structure

```
src/Capability/
  ModuleA/
  ModuleB/
  ModuleC/
```

If ModuleA imports from ModuleB, ModuleB imports from ModuleC, and ModuleC imports back to ModuleA, a circular dependency is detected.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\CircularModuleDependencyRule
        arguments:
            baseNamespace: 'App\Capability'
        tags:
            - phpstan.rules.rule
```

## Parameters

- `baseNamespace`: The base namespace for your capabilities/modules (e.g., `App\Capability`)

## Example Violations

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

## Note

This rule should be used together with `ModularArchitectureRule` for comprehensive architectural enforcement. The `ModularArchitectureRule` handles layer and cross-module dependencies, while this rule specifically handles circular dependency detection.

