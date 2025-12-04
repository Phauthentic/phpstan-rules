<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Regression test for ignoreUseStatements feature
 *
 * This test ensures that when use statements exceed the line length limit,
 * they are properly ignored when ignoreUseStatements is true.
 *
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleIgnoreUseLongLinesTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Ignore use statements (3rd parameter is true)
        return new MaxLineLengthRule(80, [], true);
    }

    /**
     * Test that long use statements are ignored when ignoreUseStatements is true,
     * but other long lines are still detected.
     *
     * This is a regression test for a bug where use statements were not properly
     * ignored because the check only looked at the node type, not the line itself.
     */
    public function testLongUseStatementsAreIgnoredButOtherLongLinesAreDetected(): void
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
