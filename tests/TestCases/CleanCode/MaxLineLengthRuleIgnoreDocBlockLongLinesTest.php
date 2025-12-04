<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test that long docblock lines are ignored when ignoreDocBlocks is true
 *
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleIgnoreDocBlockLongLinesTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Ignore docblocks (docBlocks is true)
        return new MaxLineLengthRule(80, [], false, ['docBlocks' => true]);
    }

    /**
     * Test that long docblock lines are ignored when ignoreDocBlocks is true,
     * but other long lines are still detected.
     */
    public function testLongDocBlockLinesAreIgnoredButOtherLongLinesAreDetected(): void
    {
        // Lines 6, 7, 17 have long docblock lines - should be ignored
        // Line 19 has a method signature that exceeds 80 characters - should be detected
        // Line 21 has a variable assignment that exceeds 80 characters - should be detected
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthLongDocBlockClass.php'], [
            [
                'Line 19 exceeds the maximum length of 80 characters (found 117 characters).',
                19,
            ],
            [
                'Line 21 exceeds the maximum length of 80 characters (found 114 characters).',
                21,
            ],
        ]);
    }
}
