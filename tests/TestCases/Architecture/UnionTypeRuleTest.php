<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodMustReturnTypeRule>
 */
class UnionTypeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodMustReturnTypeRule([
            [
                'pattern' => '/^UnionTypeTestClass::validOneOfInt$/',
                'nullable' => false,
                'void' => false,
                'oneOf' => ['int', 'string', 'bool'],
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^UnionTypeTestClass::validOneOfString$/',
                'nullable' => false,
                'void' => false,
                'oneOf' => ['int', 'string', 'bool'],
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^UnionTypeTestClass::validOneOfBool$/',
                'nullable' => false,
                'void' => false,
                'oneOf' => ['int', 'string', 'bool'],
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^UnionTypeTestClass::invalidOneOf$/',
                'nullable' => false,
                'void' => false,
                'oneOf' => ['int', 'string', 'bool'],
                'objectTypePattern' => null,
            ],
            [
                'pattern' => '/^UnionTypeTestClass::invalidAllOf$/',
                'nullable' => false,
                'void' => false,
                'allOf' => ['int', 'string'],
                'objectTypePattern' => null,
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/UnionTypeTestClass.php'], [
            [
                'Method UnionTypeTestClass::invalidOneOf must have one of the return types: int, string, bool, float given.',
                15,
            ],
            [
                'Method UnionTypeTestClass::invalidAllOf must have all of the return types: int, string, bool given.',
                16,
            ],
        ]);
    }
}
