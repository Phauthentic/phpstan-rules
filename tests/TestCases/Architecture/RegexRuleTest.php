<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodMustReturnTypeRule>
 */
class RegexRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodMustReturnTypeRule([
            [
                'pattern' => '/^RegexTestClass::validUserEntity$/',
                'anyOf' => ['regex:/^UserEntity$/', 'void'],
            ],
            [
                'pattern' => '/^RegexTestClass::validProductEntity$/',
                'anyOf' => ['regex:/^ProductEntity$/', 'int'],
            ],
            [
                'pattern' => '/^RegexTestClass::validVoid$/',
                'anyOf' => ['regex:/^UserEntity$/', 'void'],
            ],
            [
                'pattern' => '/^RegexTestClass::validInt$/',
                'anyOf' => ['regex:/^ProductEntity$/', 'int'],
            ],
            [
                'pattern' => '/^RegexTestClass::invalidOtherClass$/',
                'anyOf' => ['regex:/^UserEntity$/', 'regex:/^ProductEntity$/'],
            ],
            [
                'pattern' => '/^RegexTestClass::invalidString$/',
                'anyOf' => ['regex:/^UserEntity$/', 'regex:/^ProductEntity$/'],
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/RegexTestClass.php'], [
            [
                'Method RegexTestClass::invalidOtherClass must have one of the return types: regex:/^UserEntity$/, regex:/^ProductEntity$/, OtherClass given.',
                12,
            ],
            [
                'Method RegexTestClass::invalidString must have one of the return types: regex:/^UserEntity$/, regex:/^ProductEntity$/, string given.',
                13,
            ],
        ]);
    }
}
