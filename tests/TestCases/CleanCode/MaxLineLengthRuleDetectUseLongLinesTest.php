<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test that long use statements ARE detected when ignoreUseStatements is false
 * 
 * This complements the regression test to ensure the fix doesn't break
 * the default behavior of detecting long use statements.
 * 
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleDetectUseLongLinesTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Do NOT ignore use statements (3rd parameter is false/default)
        return new MaxLineLengthRule(80, [], false);
    }

    /**
     * Test that long use statements ARE detected when ignoreUseStatements is false
     */
    public function testLongUseStatementsAreDetectedWhenNotIgnored(): void
    {
        // Lines 5, 6, 7 have use statements that exceed 80 characters - should be detected
        // Line 16 has a method signature that exceeds 80 characters - should be detected
        // Line 18 has a variable assignment that exceeds 80 characters - should be detected
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthLongUseStatementsClass.php'], [
            [
                'Line 5 exceeds the maximum length of 80 characters (found 93 characters).',
                5,
            ],
            [
                'Line 6 exceeds the maximum length of 80 characters (found 93 characters).',
                6,
            ],
            [
                'Line 7 exceeds the maximum length of 80 characters (found 94 characters).',
                7,
            ],
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

