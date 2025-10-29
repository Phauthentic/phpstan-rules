<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleIgnoreUseTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Ignore use statements (3rd parameter is true)
        return new MaxLineLengthRule(80, [], true);
    }

    public function testUseStatementsAreIgnored(): void
    {
        // All use statements in this file are very long, but should be ignored
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthUseStatementsClass.php'], []);
    }
}

