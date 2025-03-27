<?php declare(strict_types = 1);

namespace App;

use Phauthentic\PhpstanRules\ControlStructureNestingRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ControlStructureNestingRule>
 */
class ControlStructureNestingRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ControlStructureNestingRule(2); // Set the maximum nesting level to 2
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../data/NestedControlStructures.php'], [
            [
                'Nesting level of 3 exceeded. Maximum allowed is 2.',
                11,
            ],
            [
                'Nesting level of 3 exceeded. Maximum allowed is 2.',
                22,
            ],
            [
                'Nesting level of 4 exceeded. Maximum allowed is 2.',
                24,
            ],
            [
                'Nesting level of 3 exceeded. Maximum allowed is 2.',
                27,
            ],
            [
                'Nesting level of 3 exceeded. Maximum allowed is 2.',
                39,
            ],
            [
                'Nesting level of 3 exceeded. Maximum allowed is 2.',
                42,
            ],
            [
                'Nesting level of 3 exceeded. Maximum allowed is 2.',
                46,
            ],
            [
                'Nesting level of 4 exceeded. Maximum allowed is 2.',
                47,
            ],
            [
                'Nesting level of 4 exceeded. Maximum allowed is 2.',
                50,
            ],
        ]);
    }
}
