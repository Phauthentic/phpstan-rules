# Rules

Add them to your `phpstan.neon` configuration file under the section `services`.

For detailed documentation of each rule, see the individual rule documentation files in the [rules/](rules/) directory.

## Modular Architecture Rule

Enforces strict dependency rules for modular hexagonal (Ports and Adapters) architecture with capabilities/modules. This rule is specifically designed for modular monoliths where each capability/module follows a layered architecture pattern.

See [Modular Architecture Rule documentation](rules/Modular-Architecture-Rule.md) for detailed information.

## Circular Module Dependency Rule

Detects circular dependencies between modules in a modular architecture. This rule tracks module-to-module dependencies and reports when circular dependencies are detected.

See [Circular Module Dependency Rule documentation](rules/Circular-Module-Dependency-Rule.md) for detailed information.

## Forbidden Accessors Rule

Forbids public and/or protected getters and setters on classes matching specified patterns. Useful for enforcing immutability or the "Tell, Don't Ask" principle.

See [Forbidden Accessors Rule documentation](rules/Forbidden-Accessors-Rule.md) for detailed information.

## Property Must Match Rule

Ensures that classes matching specified patterns have properties with expected names, types, and visibility scopes. Can optionally enforce that matching classes must have certain properties.

See [Property Must Match Rule documentation](rules/Property-Must-Match-Rule.md) for detailed information.

## Full Configuration Example

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
    # Note: Use ForbiddenDependenciesRule (DependencyConstraintsRule is deprecated)
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenDependenciesRule
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

    # Property rules for entities
    -
        class: Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule
        arguments:
            propertyPatterns:
                -
                    classPattern: '/^App\\Capability\\.*\\Domain\\Model\\.*Entity$/'
                    properties:
                        -
                            name: 'id'
                            type: 'int'
                            visibilityScope: 'private'
                            required: true
                        -
                            name: 'createdAt'
                            type: 'DateTimeImmutable'
                            visibilityScope: 'private'
                            required: true
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

    # Forbid accessors on domain entities
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule
        arguments:
            classPatterns:
                - '/^App\\Capability\\.*\\Domain\\Model\\.*Entity$/'
            forbidGetters: true
            forbidSetters: true
            visibility:
                - public
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
