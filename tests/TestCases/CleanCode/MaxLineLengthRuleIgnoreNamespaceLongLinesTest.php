<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test that long namespace declarations are ignored when ignoreNamespaces is true
 *
 * @extends RuleTestCase<MaxLineLengthRule>
 */
class MaxLineLengthRuleIgnoreNamespaceLongLinesTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Ignore namespaces (namespaceDeclaration is true)
        return new MaxLineLengthRule(80, [], false, ['namespaceDeclaration' => true]);
    }

    /**
     * Test that long namespace declarations are ignored when ignoreNamespaces is true,
     * but other long lines are still detected.
     */
    public function testLongNamespacesAreIgnoredButOtherLongLinesAreDetected(): void
    {
        // Line 3 has namespace that exceeds 80 characters - should be ignored
        // Line 12 has a method signature that exceeds 80 characters - should be detected
        // Line 14 has a variable assignment that exceeds 80 characters - should be detected
        $this->analyse([__DIR__ . '/../../../data/MaxLineLengthLongNamespaceClass.php'], [
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
