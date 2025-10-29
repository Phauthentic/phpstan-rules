<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustHaveSpecificationDocblockRule;
use PHPStan\Testing\RuleTestCase;

/**
 * Tests for ClassMustHaveSpecificationDocblockRule with interfaces and interface methods.
 *
 * @extends RuleTestCase<ClassMustHaveSpecificationDocblockRule>
 */
class ClassMustHaveSpecificationDocblockRuleInterfaceTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustHaveSpecificationDocblockRule(
            classPatterns: [
                '/.*Interface$/',
            ],
            methodPatterns: [
                '/.*Interface::.*/',
            ]
        );
    }

    public function testInterfaceWithoutDocblockTriggersError(): void
    {
        $this->analyse([__DIR__ . '/../../../data/SpecificationDocblock/InterfaceWithoutDocblock.php'], [
            // Interface without docblock
            [
                'Interface App\Service\ServiceInterface must have a docblock with a "Specification:" section.',
                8,
            ],
            // Methods without docblocks
            [
                'Method App\Service\ServiceInterface::execute must have a docblock with a "Specification:" section.',
                10,
            ],
            [
                'Method App\Service\RepositoryInterface::delete must have a docblock with a "Specification:" section.',
                31,
            ],
        ]);
    }
}

