<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\CircularModuleDependencyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CircularModuleDependencyRule>
 */
class CircularModuleDependencyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CircularModuleDependencyRule('App\\Capability');
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Reset dependency tracking before each test
        CircularModuleDependencyRule::resetDependencyTracking();
    }

    public function testNoCircularDependencies(): void
    {
        // Simple dependency chain without cycles should pass
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/ValidCrossModule.php'],
            []
        );
    }

    public function testNonModularNamespaceIsSkipped(): void
    {
        // A class outside the modular namespace should be ignored
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/NonModular/OutsideClass.php'],
            []
        );
    }

    public function testSameModuleImportIsSkipped(): void
    {
        // Importing from the same module should not trigger any errors
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Application/SameModuleImport.php'],
            []
        );
    }

    public function testModularFileImportingNonModularClassIsSkipped(): void
    {
        // A modular file importing a non-modular class (e.g., DateTime) should be skipped
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/NonModularImport.php'],
            []
        );
    }

    public function testCircularDependencyDetection(): void
    {
        // Reset to ensure clean state
        CircularModuleDependencyRule::resetDependencyTracking();

        // Analyze files in order to build up the circular dependency
        // Step 1: ProductCatalog → Billing
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/ProductCatalog/Application/CreateCircular.php'],
            []
        );

        // Step 2: Billing → UserManagement
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/Billing/Application/CircularDep.php'],
            []
        );

        // Step 3: UserManagement → ProductCatalog (creates circular dependency)
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Application/CreateCircularToUserManagement.php'],
            [
                [
                    'Circular dependency detected: UserManagement → ProductCatalog → Billing → UserManagement',
                    7,
                ],
            ]
        );
    }
}
