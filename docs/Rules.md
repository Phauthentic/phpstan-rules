# Rules

Add them to your `phpstan.neon` configuration file under the section `services`.

For detailed documentation of each rule, see the individual rule documentation files in the [rules/](rules/) directory.

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
