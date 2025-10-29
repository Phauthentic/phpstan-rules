<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MaxLineLengthRule(80); // Set maximum line length to 80 characters
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthTestClass.php'], [
            [
                'Line 7 exceeds the maximum length of 80 characters (found 84 characters).',
                7,
            ],
        ]);
    }

    public function testMultipleLongLinesInFile(): void
    {
        // Test that multiple long lines in the same file are all detected
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthMultipleLines.php'], [
            [
                'Line 5 exceeds the maximum length of 80 characters (found 115 characters).',
                5,
            ],
            [
                'Line 7 exceeds the maximum length of 80 characters (found 110 characters).',
                7,
            ],
        ]);
    }
}
