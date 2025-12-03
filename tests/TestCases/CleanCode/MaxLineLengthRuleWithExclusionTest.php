<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleWithExclusionTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Exclude files matching "Excluded" in their path
        return new MaxLineLengthRule(80, ['/.*Excluded.*/']);
    }

    public function testExcludedFileIsNotChecked(): void
    {
        // This file should be excluded, so no errors should be reported
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthExcludedClass.php'], []);
    }

    public function testNonExcludedFileIsChecked(): void
    {
        // This file is NOT excluded, so errors should be reported
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthTestClass.php'], [
            [
                'Line 7 exceeds the maximum length of 80 characters (found 84 characters).',
                7,
            ],
        ]);
    }
}
