<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test that long namespace declarations ARE detected when ignoreNamespaces is false
 * 
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleDetectNamespaceLongLinesTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Do NOT ignore namespaces (namespaceDeclaration is false/default)
        return new MaxLineLengthRule(80, [], false, ['namespaceDeclaration' => false]);
    }

    /**
     * Test that long namespace declarations ARE detected when ignoreNamespaces is false
     */
    public function testLongNamespacesAreDetectedWhenNotIgnored(): void
    {
        // Line 3 has namespace that exceeds 80 characters - should be detected
        // Line 12 has a method signature that exceeds 80 characters - should be detected
        // Line 14 has a variable assignment that exceeds 80 characters - should be detected
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthLongNamespaceClass.php'], [
            [
                'Line 3 exceeds the maximum length of 80 characters (found 101 characters).',
                3,
            ],
            [
                'Line 12 exceeds the maximum length of 80 characters (found 117 characters).',
                12,
            ],
            [
                'Line 14 exceeds the maximum length of 80 characters (found 114 characters).',
                14,
            ],
        ]);
    }
}

