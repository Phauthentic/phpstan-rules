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
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/RegexAllOfTestClass.php'], [
            [
                'Method RegexAllOfTestClass::validUnionWithUser must have a return type of all of: regex:/^UserEntity$/, int.',
                6,
            ],
            [
                'Method RegexAllOfTestClass::validUnionWithProduct must have a return type of all of: regex:/^ProductEntity$/, string.',
                7,
            ],
            [
                'Method RegexAllOfTestClass::invalidUnionMissingUser must have a return type of all of: regex:/^UserEntity$/, int.',
                10,
            ],
            [
                'Method RegexAllOfTestClass::invalidUnionMissingProduct must have a return type of all of: regex:/^ProductEntity$/, string.',
                11,
            ],
        ]);
    }
} 