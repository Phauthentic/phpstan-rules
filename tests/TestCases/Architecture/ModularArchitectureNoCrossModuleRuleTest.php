<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ModularArchitectureRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test that without cross-module patterns, all cross-module imports are blocked
 *
 * @extends RuleTestCase<ModularArchitectureRule>
 */
class ModularArchitectureNoCrossModuleRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // No cross-module patterns - blocks all cross-module imports
        return new ModularArchitectureRule('App\\Capability', null, []);
    }

    public function testNoCrossModulePatternsBlocksAllImports(): void
    {
        // Without any cross-module patterns, even Facade/Input/Result should be blocked
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/ValidCrossModule.php'],
            [
                [
                    'Cross-module violation: Module `ProductCatalog` is not allowed to import `App\Capability\UserManagement\UserManagementFacade` from module `UserManagement`.',
                    7,
                ],
                [
                    'Cross-module violation: Module `ProductCatalog` is not allowed to import `App\Capability\UserManagement\UserManagementFacadeInterface` from module `UserManagement`.',
                    8,
                ],
                [
                    'Cross-module violation: Module `ProductCatalog` is not allowed to import `App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserInput` from module `UserManagement`.',
                    9,
                ],
                [
                    'Cross-module violation: Module `ProductCatalog` is not allowed to import `App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserResult` from module `UserManagement`.',
                    10,
                ],
            ]
        );
    }

    public function testIntraModuleDependenciesStillWork(): void
    {
        // Intra-module dependencies should still work (layer rules)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Application/UseCases/CreateUser/CreateUser.php'],
            []
        );
    }
}
