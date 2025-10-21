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

    public function testRuleWithExcludePatterns(): void
    {
        $rule = new MaxLineLengthRule(80, ['/.*Excluded.*/']);

        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthExcludedClass.php'], [
            [
                'Line 7 exceeds the maximum length of 80 characters (found 81 characters).',
                7,
            ],
            [
                'Line 9 exceeds the maximum length of 80 characters (found 86 characters).',
                9,
            ],
        ]);
    }



    public function testRuleWithIgnoreUseStatements(): void
    {
        $rule = new MaxLineLengthRule(80, [], true);

        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthUseStatementsClass.php'], []);
    }
}
