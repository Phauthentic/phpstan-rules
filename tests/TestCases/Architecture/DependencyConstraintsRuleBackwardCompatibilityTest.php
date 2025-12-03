<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\DependencyConstraintsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test backward compatibility - FQCN checking disabled by default
 *
 * @extends RuleTestCase<DependencyConstraintsRule>
 */
class DependencyConstraintsRuleBackwardCompatibilityTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Default behavior - checkFqcn is false
        return new DependencyConstraintsRule([
            '/^App\\\\Capability(?:\\\\\\w+)*$/' => ['/^DateTime$/', '/^DateTimeImmutable$/']
        ]);
    }

    /**
     * Test that FQCN checking is disabled by default
     * Only use statements should be caught, not FQCN references
     */
    public function testFqcnCheckingDisabledByDefault(): void
    {
        // Should only catch the use statements, not the FQCN usages
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/MixedUsageForbidden.php'], [
            [
                'Dependency violation: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                10,
            ],
            [
                'Dependency violation: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                11,
            ],
        ]);
    }

    /**
     * Test that FQCN instantiations are not caught when disabled
     */
    public function testFqcnInstantiationNotCaught(): void
    {
        // Should have no errors since we're only checking use statements
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/NewInstantiation.php'], []);
    }

    /**
     * Test that FQCN type hints are not caught when disabled
     */
    public function testFqcnTypeHintsNotCaught(): void
    {
        // Should have no errors since we're only checking use statements
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/TypeHints.php'], []);
    }

    /**
     * Test that existing functionality still works (use statements are caught)
     */
    public function testExistingFunctionalityWorks(): void
    {
        // This is the original test from NamespaceDependencyRuleTest
        // to ensure backward compatibility
        $this->analyse([__DIR__ . '/../../../data/DependencyRuleTest/Domain/Aggregate.php'], []);
    }
}
