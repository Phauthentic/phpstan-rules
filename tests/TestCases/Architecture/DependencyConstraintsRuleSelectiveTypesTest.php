<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\DependencyConstraintsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test selective reference type checking
 * Tests that only specified reference types are checked when configured
 *
 * @extends RuleTestCase<DependencyConstraintsRule>
 */
class DependencyConstraintsRuleSelectiveTypesTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Only check 'new' and 'return' reference types
        return new DependencyConstraintsRule(
            ['/^App\\\\Capability(?:\\\\\\w+)*$/' => ['/^DateTime$/']],
            true,
            ['new', 'return']
        );
    }

    /**
     * Test that only selected reference types are checked
     * Should catch 'new' and 'return' but not 'property', 'param', 'static_call', etc.
     */
    public function testSelectiveReferenceTypes(): void
    {
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/SelectiveReferenceTypes.php'], [
            [
                'Dependency violation: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                19, // Return type hint
            ],
            [
                'Dependency violation: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                27, // New instantiation
            ],
        ]);
    }
}
