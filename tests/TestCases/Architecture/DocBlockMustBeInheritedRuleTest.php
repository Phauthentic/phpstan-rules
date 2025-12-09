<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\DocBlockMustBeInheritedRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DocBlockMustBeInheritedRule>
 */
class DocBlockMustBeInheritedRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DocBlockMustBeInheritedRule([
            '/^App\\\\Test\\\\ValidInheritDocClass::method/',
            '/^App\\\\Test\\\\InvalidMissingDocBlockClass::method/',
            '/^App\\\\Test\\\\InvalidMissingInheritDocClass::(method|another)/',
            '/^App\\\\Test\\\\MixedClass::matching/',
        ]);
    }

    public function testValidInheritDoc(): void
    {
        // Test a file with valid @inheritDoc usage - should have no errors
        $this->analyse([__DIR__ . '/../../../data/DocBlockMustBeInherited/ValidInheritDocClass.php'], []);
    }

    public function testInvalidMissingDocBlock(): void
    {
        // Test a file with missing docblock
        $this->analyse([__DIR__ . '/../../../data/DocBlockMustBeInherited/InvalidMissingDocBlockClass.php'], [
            [
                'Method App\Test\InvalidMissingDocBlockClass::methodWithoutDocBlock must have a docblock with @inheritDoc or @inheritdoc.',
                7,
            ],
        ]);
    }

    public function testInvalidMissingInheritDoc(): void
    {
        // Test a file with docblock but no @inheritDoc
        $this->analyse([__DIR__ . '/../../../data/DocBlockMustBeInherited/InvalidMissingInheritDocClass.php'], [
            [
                'Method App\Test\InvalidMissingInheritDocClass::methodWithoutInheritDoc docblock must contain @inheritDoc or @inheritdoc.',
                12,
            ],
            [
                'Method App\Test\InvalidMissingInheritDocClass::anotherMethodWithoutInheritDoc docblock must contain @inheritDoc or @inheritdoc.',
                19,
            ],
        ]);
    }

    public function testMixedScenarios(): void
    {
        // Test a file with mixed valid/invalid methods
        $this->analyse([__DIR__ . '/../../../data/DocBlockMustBeInherited/MixedClass.php'], [
            [
                'Method App\Test\MixedClass::matchingMethodInvalid docblock must contain @inheritDoc or @inheritdoc.',
                21,
            ],
            [
                'Method App\Test\MixedClass::matchingMethodNoDocblock must have a docblock with @inheritDoc or @inheritdoc.',
                26,
            ],
        ]);
    }
}
