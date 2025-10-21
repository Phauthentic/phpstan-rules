<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodMustReturnTypeRule>
 */
class AnyOfRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodMustReturnTypeRule([
            [
                'pattern' => '/^AnyOfTestClass::[a-zA-Z]+$/',
                'anyOf' => ['object', 'void'],
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/AnyOfTestClass.php'], [
            [
                'Method AnyOfTestClass::validObject must have one of the return types: object, void, SomeObject given.',
                6,
            ],
            [
                'Method AnyOfTestClass::invalidType must have one of the return types: object, void, int given.',
                10,
            ],
        ]);
    }
}
