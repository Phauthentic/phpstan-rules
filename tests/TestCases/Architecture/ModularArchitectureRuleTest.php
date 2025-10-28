<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ModularArchitectureRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ModularArchitectureRule>
 */
class ModularArchitectureRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ModularArchitectureRule(
            'App\\Capability',
            null,
            [
                '/Facade$/',                    // Class names ending with Facade
                '/FacadeInterface$/',           // Class names ending with FacadeInterface
                '/Input$/',                     // Class names ending with Input
                '/Result$/',                    // Class names ending with Result
            ]
        );
    }


    public function testValidDomainEntity(): void
    {
        // Valid domain entity with no violations
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Domain/Model/User.php'],
            []
        );
    }

    public function testDomainCannotImportApplication(): void
    {
        // Domain layer importing from Application layer (violation)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Domain/InvalidDomain.php'],
            [
                [
                    'Layer violation: Domain layer cannot depend on Application layer. Class in `App\Capability\UserManagement\Domain` cannot import `App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUser`.',
                    7,
                ],
            ]
        );
    }

    public function testApplicationCannotImportPresentation(): void
    {
        // Application layer importing from Presentation layer (violation)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Application/InvalidApp.php'],
            [
                [
                    'Layer violation: Application layer cannot depend on Presentation layer. Class in `App\Capability\UserManagement\Application` cannot import `App\Capability\UserManagement\Presentation\AdminAPI\Controller\UserController`.',
                    7,
                ],
            ]
        );
    }

    public function testInfrastructureCannotImportPresentation(): void
    {
        // Infrastructure layer importing from Presentation layer (violation)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Infrastructure/InvalidInfra.php'],
            [
                [
                    'Layer violation: Infrastructure layer cannot depend on Presentation layer. Class in `App\Capability\UserManagement\Infrastructure` cannot import `App\Capability\UserManagement\Presentation\AdminAPI\Controller\UserController`.',
                    7,
                ],
            ]
        );
    }

    public function testPresentationCannotImportInfrastructure(): void
    {
        // Presentation layer importing from Infrastructure layer (violation)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Presentation/InvalidPresentation.php'],
            [
                [
                    'Layer violation: Presentation layer cannot depend on Infrastructure layer. Class in `App\Capability\UserManagement\Presentation` cannot import `App\Capability\UserManagement\Infrastructure\InvalidInfra`.',
                    7,
                ],
            ]
        );
    }

    public function testValidPresentationImports(): void
    {
        // Presentation can import from Application and Domain (valid)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Presentation/AdminAPI/Controller/UserController.php'],
            []
        );
    }

    public function testLayerCanImportFromItself(): void
    {
        // A layer should be able to import from itself (same layer, same module)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Presentation/ValidPresentationImport.php'],
            []
        );
    }

    public function testValidApplicationImportsDomain(): void
    {
        // Application can import from Domain (valid)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Application/UseCases/CreateUser/CreateUser.php'],
            []
        );
    }

    public function testValidCrossModuleFacadeImports(): void
    {
        // Valid cross-module imports: Facade, FacadeInterface, Input, Result
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/ValidCrossModule.php'],
            []
        );
    }

    public function testInvalidCrossModuleExceptionImport(): void
    {
        // Invalid: Importing exception from another module
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/InvalidCrossModule.php'],
            [
                [
                    'Cross-module violation: Module `ProductCatalog` is not allowed to import `App\Capability\UserManagement\UserManagementException` from module `UserManagement`.',
                    7,
                ],
            ]
        );
    }

    public function testValidFacadeFiles(): void
    {
        // Facades should be analyzable without errors
        $this->analyse(
            [
                __DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/UserManagementFacade.php',
                __DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/UserManagementFacadeInterface.php',
            ],
            []
        );
    }

    public function testValidInputAndResultDTOs(): void
    {
        // Input and Result DTOs should be analyzable without errors
        $this->analyse(
            [
                __DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Application/UseCases/CreateUser/CreateUserInput.php',
                __DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Application/UseCases/CreateUser/CreateUserResult.php',
            ],
            []
        );
    }

    public function testCustomDtoNotAllowedByDefault(): void
    {
        // With default config, classes ending in "Dto" (not Input/Result) should not be allowed cross-module
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/UseCustomDto.php'],
            [
                [
                    'Cross-module violation: Module `ProductCatalog` is not allowed to import `App\Capability\UserManagement\UserManagementDto` from module `UserManagement`.',
                    7,
                ],
            ]
        );
    }
}

