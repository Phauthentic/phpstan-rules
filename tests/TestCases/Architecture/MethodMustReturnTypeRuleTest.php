<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodMustReturnTypeRule>
 */
class MethodMustReturnTypeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodMustReturnTypeRule([
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnInt$/',
                'type' => 'int',
                'nullable' => false,
                'void' => false,
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnNullableString$/',
                'type' => 'string',
                'nullable' => true,
                'void' => false,
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnVoid$/',
                'type' => 'void',
                'nullable' => false,
                'void' => false,
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnVoidLegacy$/',
                'nullable' => false,
                'void' => true,
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnSpecificObject$/',
                'type' => 'object',
                'nullable' => false,
                'void' => false,
                'objectTypePattern' => '/^SomeObject$/',
            ],
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnOneOf$/',
                'nullable' => false,
                'void' => false,
                'oneOf' => ['int', 'string', 'bool'],
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnAllOf$/',
                'nullable' => false,
                'void' => false,
                'allOf' => ['int', 'string'],
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnOneOfNullable$/',
                'nullable' => true,
                'void' => false,
                'oneOf' => ['int', 'string'],
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^ReturnTypeTestClass::mustReturnNullableObject$/',
                'type' => 'object',
                'nullable' => true,
                'void' => false,
                'objectTypePattern' => null,
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/ReturnTypeTestClass.php'], [
            [
                'Method ReturnTypeTestClass::mustReturnInt must have return type int, void given.',
                6,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnNullableString return type nullability does not match: expected nullable.',
                7,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnVoid must have return type void, int given.',
                8,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnVoidLegacy must have a void return type.',
                9,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnSpecificObject must return an object matching pattern /^SomeObject$/, OtherObject given.',
                10,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnOneOf must have one of the return types: int, string, bool, float given.',
                11,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnAllOf must have all of the return types: int, string, int given.',
                12,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnOneOfNullable return type nullability does not match: expected nullable.',
                13,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnNullableObject return type nullability does not match: expected nullable.',
                14,
            ],
        ]);
    }
}
