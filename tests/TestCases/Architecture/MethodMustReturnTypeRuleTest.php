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
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/ReturnTypeTestClass.php'], [
            [
                'Method ReturnTypeTestClass::mustReturnInt must have return type int, void given.',
                5,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnNullableString return type nullability does not match: expected nullable.',
                6,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnVoid must have a void return type.',
                7,
            ],
            [
                'Method ReturnTypeTestClass::mustReturnSpecificObject must return an object matching pattern /^SomeObject$/, OtherObject given.',
                8,
            ],
        ]);
    }
}
