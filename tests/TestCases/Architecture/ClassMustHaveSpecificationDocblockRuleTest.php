<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustHaveSpecificationDocblockRule>
 */
class ClassMustHaveSpecificationDocblockRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustHaveSpecificationDocblockRule(
            classPatterns: [
                '/.*/', // Match all classes for testing
            ]
        );
    }

    public function testValidSpecificationClass(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidSpecificationClass.php'], []);
    }

    public function testValidSpecificationWithAnnotations(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidSpecificationWithAnnotationsClass.php'], []);
    }

    public function testValidSpecificationWithTextAndAnnotations(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/ValidSpecificationWithTextAndAnnotationsClass.php'], []);
    }

    public function testMissingDocblock(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/MissingDocblockClass.php'], [
            [
                'Class App\SpecificationDocblock\MissingDocblockClass must have a docblock with a "Specification:" section.',
                7,
            ],
        ]);
    }

    public function testMissingSpecificationHeader(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/MissingSpecificationHeaderClass.php'], [
            [
                'Class App\SpecificationDocblock\MissingSpecificationHeaderClass has an invalid specification docblock. Expected format: "Specification:" header, blank line, then list items starting with "-".',
                12,
            ],
        ]);
    }

    public function testInvalidSpecificationNoBlankLine(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/InvalidSpecificationNoBlankLineClass.php'], [
            [
                'Class App\SpecificationDocblock\InvalidSpecificationNoBlankLineClass has an invalid specification docblock. Expected format: "Specification:" header, blank line, then list items starting with "-".',
                11,
            ],
        ]);
    }

    public function testInvalidSpecificationNoListItem(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/InvalidSpecificationNoListItemClass.php'], [
            [
                'Class App\SpecificationDocblock\InvalidSpecificationNoListItemClass has an invalid specification docblock. Expected format: "Specification:" header, blank line, then list items starting with "-".',
                12,
            ],
        ]);
    }
}


