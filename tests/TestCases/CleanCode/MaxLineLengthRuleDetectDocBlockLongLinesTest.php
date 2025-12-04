<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test that long docblock lines ARE detected when ignoreDocBlocks is false
 * 
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleDetectDocBlockLongLinesTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Do NOT ignore docblocks (docBlocks is false/default)
        return new MaxLineLengthRule(80, [], false, ['docBlocks' => false]);
    }

    /**
     * Test that long docblock lines ARE detected when ignoreDocBlocks is false
     */
    public function testLongDocBlockLinesAreDetectedWhenNotIgnored(): void
    {
        // Line 6 has a long docblock line - should be detected
        // Line 7 has a long docblock line - should be detected
        // Line 17 has a long docblock line - should be detected
        // Line 19 has a method signature that exceeds 80 characters - should be detected
        // Line 21 has a variable assignment that exceeds 80 characters - should be detected
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthLongDocBlockClass.php'], [
            [
                'Line 6 exceeds the maximum length of 80 characters (found 111 characters).',
                6,
            ],
            [
                'Line 7 exceeds the maximum length of 80 characters (found 113 characters).',
                7,
            ],
            [
                'Line 17 exceeds the maximum length of 80 characters (found 121 characters).',
                17,
            ],
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

