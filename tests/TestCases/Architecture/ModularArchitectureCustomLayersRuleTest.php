<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ModularArchitectureRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test custom layer configuration
 * 
 * @extends RuleTestCase<ModularArchitectureRule>
 */
class ModularArchitectureCustomLayersRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Custom configuration: Allow Application to depend on Infrastructure
        return new ModularArchitectureRule('App\\Capability', [
            'Domain' => [],
            'Application' => ['Domain', 'Infrastructure'], // Custom: Application CAN depend on Infrastructure
            'Infrastructure' => ['Domain'],
            'Presentation' => ['Application', 'Domain'],
        ]);
    }


    public function testApplicationCanImportInfrastructureWithCustomConfig(): void
    {
        // With custom config allowing Application → Infrastructure, this should pass without errors
        $this->analyse(
            [__DIR__ . '/../../../data/ModularArchitectureTest/Capability/UserManagement/Application/ValidWithCustomConfig.php'],
            []
        );
    }

    public function testApplicationStillCannotImportPresentation(): void
    {
        // Even with custom config, Application still cannot import Presentation
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

    public function testDomainStillCannotImportApplication(): void
    {
        // Domain layer should still not be able to import Application
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
}
