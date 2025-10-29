<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustHaveSpecificationDocblockRule>
 */
class ClassMustHaveSpecificationDocblockRuleNoBlankLineTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustHaveSpecificationDocblockRule(
            classPatterns: ['/.*/'  // Match all classes
            ],
            methodPatterns: [],
            specificationHeader: 'Specification:',
            requireBlankLineAfterHeader: false
        );
    }

    public function testValidNoBlankLineAfterHeader(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidNoBlankLineAfterHeaderClass.php'], []);
    }

    public function testInvalidWithBlankLineWhenNotRequired(): void
    {
        // When blank line is not required, having one should still be valid
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidSpecificationClass.php'], []);
    }
}

