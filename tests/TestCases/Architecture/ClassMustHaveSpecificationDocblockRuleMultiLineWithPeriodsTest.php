<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustHaveSpecificationDocblockRule>
 */
class ClassMustHaveSpecificationDocblockRuleMultiLineWithPeriodsTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustHaveSpecificationDocblockRule(
            patterns: ['/.*/'],
            specificationHeader: 'Specification:',
            requireBlankLineAfterHeader: true,
            requireListItemsEndWithPeriod: true
        );
    }

    public function testValidMultiLineWithPeriods(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidMultiLineWithPeriodClass.php'], []);
    }

    public function testInvalidMultiLineNoPeriod(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/InvalidMultiLineNoPeriodClass.php'], [
            [
                'Class App\SpecificationDocblock\InvalidMultiLineNoPeriodClass has an invalid specification docblock. Expected format: "Specification:" header, blank line, then list items starting with "-". List items must end with a period.',
                14,
            ],
        ]);
    }
}

