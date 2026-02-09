<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodMustReturnTypeRule>
 */
class RegexAllOfRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodMustReturnTypeRule([
            [
                'pattern' => '/^RegexAllOfTestClass::validUnionWithUser$/',
                'allOf' => ['regex:/^UserEntity$/', 'int'],
            ],
            [
                'pattern' => '/^RegexAllOfTestClass::validUnionWithProduct$/',
                'allOf' => ['regex:/^ProductEntity$/', 'string'],
            ],
            [
                'pattern' => '/^RegexAllOfTestClass::invalidUnionMissingUser$/',
                'allOf' => ['regex:/^UserEntity$/', 'int'],
            ],
            [
                'pattern' => '/^RegexAllOfTestClass::invalidUnionMissingProduct$/',
                'allOf' => ['regex:/^ProductEntity$/', 'string'],
            ],
        ]);
    }

    public function testRule(): void
    {
        // With the improved getTypeAsString() from ClassNameResolver trait,
        // union types are now properly parsed, so the valid cases pass.
        // Only the invalid cases (missing required types) should report errors.
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/RegexAllOfTestClass.php'], [
            [
                'Method RegexAllOfTestClass::invalidUnionMissingUser must have all of the return types: regex:/^UserEntity$/, int, OtherClass|int given.',
                10,
            ],
            [
                'Method RegexAllOfTestClass::invalidUnionMissingProduct must have all of the return types: regex:/^ProductEntity$/, string, UserEntity|OtherClass given.',
                11,
            ],
        ]);
    }
}
