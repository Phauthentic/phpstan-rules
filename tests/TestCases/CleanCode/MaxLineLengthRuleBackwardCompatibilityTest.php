<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test backward compatibility: ignoreUseStatements parameter still works
 * 
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleBackwardCompatibilityTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Use the old API: 3rd parameter for ignoreUseStatements
        return new MaxLineLengthRule(80, [], true);
    }

    /**
     * Test that the old ignoreUseStatements parameter still works (backward compatibility)
     */
    public function testBackwardCompatibilityWithOldIgnoreUseStatementsParameter(): void
    {
        // Lines 5, 6, 7 have use statements that exceed 80 characters - should be ignored (BC)
        // Line 16 has a method signature that exceeds 80 characters - should be detected
        // Line 18 has a variable assignment that exceeds 80 characters - should be detected
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthLongUseStatementsClass.php'], [
            [
                'Line 16 exceeds the maximum length of 80 characters (found 117 characters).',
                16,
            ],
            [
                'Line 18 exceeds the maximum length of 80 characters (found 114 characters).',
                18,
            ],
        ]);
    }
}

