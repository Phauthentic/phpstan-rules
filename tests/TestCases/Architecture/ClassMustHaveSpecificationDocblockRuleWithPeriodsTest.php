<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustHaveSpecificationDocblockRule>
 */
class ClassMustHaveSpecificationDocblockRuleWithPeriodsTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustHaveSpecificationDocblockRule(
            classPatterns: ['/.*/'],  // Match all classes
            methodPatterns: [],
            specificationHeader: 'Specification:',
            requireBlankLineAfterHeader: true,
            requireListItemsEndWithPeriod: true
        );
    }

    public function testValidWithPeriods(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidWithPeriodsClass.php'], []);
    }

    public function testInvalidMissingPeriods(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/InvalidMissingPeriodsClass.php'], [
            [
                'Class App\SpecificationDocblock\InvalidMissingPeriodsClass has an invalid specification docblock. Expected format: "Specification:" header, blank line, then list items starting with "-". List items must end with a period.',
                13,
            ],
        ]);
    }
}

