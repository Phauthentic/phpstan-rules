<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodMustReturnTypeRule>
 */
class MethodMustReturnTypeRuleEdgeCasesTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodMustReturnTypeRule([
            [
                'pattern' => '/^EdgeCaseTestClass::noReturnTypeWithType$/',
                'type' => 'int',
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::noReturnTypeWithOneOf$/',
                'oneOf' => ['int', 'string'],
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::noReturnTypeWithAllOf$/',
                'allOf' => ['int', 'string'],
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::objectReturnsInt$/',
                'type' => 'object',
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::anyOfInvalid$/',
                'anyOf' => ['int', 'string'],
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::anyOfValid$/',
                'anyOf' => ['int', 'string'],
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::regexTypeValid$/',
                'oneOf' => ['regex:/^Some.*Object$/', 'int'],
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::regexTypeInvalid$/',
                'oneOf' => ['regex:/^Some.*Object$/', 'int'],
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::validInt$/',
                'type' => 'int',
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::validNullableString$/',
                'type' => 'string',
                'nullable' => true,
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::validVoid$/',
                'void' => true,
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::validObject$/',
                'type' => 'object',
                'objectTypePattern' => '/^SomeEdgeCaseObject$/',
            ],
            [
                'pattern' => '/^EdgeCaseTestClass::validNullableObject$/',
                'type' => 'object',
                'nullable' => true,
            ],
        ]);
    }

    public function testEdgeCases(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/EdgeCaseTestClass.php'], [
            [
                'Method EdgeCaseTestClass::noReturnTypeWithType must have a return type of int.',
                5,
            ],
            [
                'Method EdgeCaseTestClass::noReturnTypeWithOneOf must have a return type of one of: int, string.',
                7,
            ],
            [
                'Method EdgeCaseTestClass::noReturnTypeWithAllOf must have a return type of all of: int, string.',
                9,
            ],
            [
                'Method EdgeCaseTestClass::objectReturnsInt must return an object type.',
                11,
            ],
            [
                'Method EdgeCaseTestClass::anyOfInvalid must have one of the return types: int, string, float given.',
                13,
            ],
            [
                'Method EdgeCaseTestClass::regexTypeInvalid must have one of the return types: regex:/^Some.*Object$/, int, float given.',
                19,
            ],
        ]);
    }
}
