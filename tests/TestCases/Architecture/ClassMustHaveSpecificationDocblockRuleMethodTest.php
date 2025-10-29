<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustHaveSpecificationDocblockRule>
 */
class ClassMustHaveSpecificationDocblockRuleMethodTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustHaveSpecificationDocblockRule(
            classPatterns: [],
            methodPatterns: [
                '/.*::testMethod$/', // Match testMethod in any class
            ]
        );
    }

    public function testValidMethodDocblock(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidMethodDocblockClass.php'], []);
    }

    public function testMissingMethodDocblock(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/MissingMethodDocblockClass.php'], [
            [
                'Method App\SpecificationDocblock\MissingMethodDocblockClass::testMethod must have a docblock with a "Specification:" section.',
                9,
            ],
        ]);
    }

    public function testInvalidMethodDocblock(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/InvalidMethodDocblockClass.php'], [
            [
                'Method App\SpecificationDocblock\InvalidMethodDocblockClass::testMethod has an invalid specification docblock. Expected format: "Specification:" header, blank line, then list items starting with "-".',
                13,
            ],
        ]);
    }
}

