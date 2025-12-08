<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test the new array API for ignore options
 *
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleArrayApiTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Use the new array API to ignore use statements via the array
        return new MaxLineLengthRule(80, [], false, ['useStatements' => true]);
    }

    /**
     * Test that the new array API works for useStatements
     */
    public function testArrayApiForUseStatements(): void
    {
        // Lines 5, 6, 7 have use statements that exceed 80 characters - should be ignored
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
