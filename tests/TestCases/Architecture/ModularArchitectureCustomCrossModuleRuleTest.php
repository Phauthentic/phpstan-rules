<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ModularArchitectureRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test custom cross-module pattern configuration
 *
 * @extends RuleTestCase<ModularArchitectureRule>
 */
class ModularArchitectureCustomCrossModuleRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Custom configuration: Allow classes ending with "Dto" to be imported cross-module
        return new ModularArchitectureRule(
            'App\\Capability',
            null, // Use default layer dependencies
            [
                '/Facade$/',                    // Keep default: Facade
                '/FacadeInterface$/',           // Keep default: FacadeInterface
                '/Input$/',                     // Keep default: Input
                '/Result$/',                    // Keep default: Result
                '/Dto$/',                       // Custom: Allow Dto classes
            ]
        );
    }


    public function testCustomDtoPatternAllowsCrossModuleImport(): void
    {
        // With custom pattern '/Dto$/', this should NOT produce an error
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/UseCustomDto.php'],
            []
        );
    }

    public function testStillBlocksNonMatchingCrossModuleImports(): void
    {
        // Exception imports should still be blocked even with custom config
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

    public function testStillAllowsDefaultFacadePattern(): void
    {
        // Facade imports should still work with custom config
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/ValidCrossModule.php'],
            []
        );
    }
}
