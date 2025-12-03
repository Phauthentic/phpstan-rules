<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustBeFinalRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustBeFinalRule>
 */
class ClassMustBeFinalRuleWithAbstractNotIgnoredTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        // Do NOT ignore abstract classes
        return new ClassMustBeFinalRule(
            patterns: ['/Service$/'],
            ignoreAbstractClasses: false
        );
    }

    public function testConcreteClassMustBeFinal(): void
    {
        // Test that concrete (non-abstract) classes are checked when ignoreAbstractClasses is false
        // This ensures the flag doesn't prevent checking of regular classes
        $this->analyse([__DIR__ . '/../../../data/Service/MissingFinalRuleService.php'], [
            [
                'Class App\Service\MissingFinalRuleService must be final.',
                5,
            ],
        ]);
    }
}
