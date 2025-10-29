<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustHaveSpecificationDocblockRule>
 */
class ClassMustHaveSpecificationDocblockRuleCustomHeaderTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustHaveSpecificationDocblockRule(
            classPatterns: ['/.*/'],  // Match all classes
            methodPatterns: [],
            specificationHeader: 'Requirements:',
            requireBlankLineAfterHeader: true
        );
    }

    public function testValidCustomHeader(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidCustomHeaderClass.php'], []);
    }

    public function testInvalidWithDefaultHeader(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidSpecificationClass.php'], [
            [
                'Class App\SpecificationDocblock\ValidSpecificationClass has an invalid specification docblock. Expected format: "Requirements:" header, blank line, then list items starting with "-".',
                12,
            ],
        ]);
    }
}

