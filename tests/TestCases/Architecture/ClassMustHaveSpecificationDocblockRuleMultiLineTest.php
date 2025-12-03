<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustHaveSpecificationDocblockRule>
 */
class ClassMustHaveSpecificationDocblockRuleMultiLineTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustHaveSpecificationDocblockRule(
            classPatterns: [
                '/.*/', // Match all classes
            ]
        );
    }

    public function testMultiLineListItems(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidMultiLineListItemClass.php'], []);
    }

    public function testMultiLineWithPeriod(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidMultiLineWithPeriodClass.php'], []);
    }

    public function testMultiLineComplexFormat(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidMultiLineComplexClass.php'], []);
    }
}
