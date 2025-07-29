<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodMustReturnTypeRule>
 */
class FacadeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodMustReturnTypeRule([
            [
                'pattern' => '/Facade::[a-zA-Z]+$/',
                'anyOf' => ['object', 'void'],
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/FacadeTestClass.php'], [
            [
                'Method Facade::someMethod must have one of the return types: object, void, SomeObject given.',
                5,
            ],
            [
                'Method Facade::invalidMethod must have one of the return types: object, void, int given.',
                7,
            ],
        ]);
    }
} 