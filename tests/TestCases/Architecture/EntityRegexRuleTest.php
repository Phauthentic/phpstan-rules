<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodMustReturnTypeRule>
 */
class EntityRegexRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodMustReturnTypeRule([
            [
                'pattern' => '/^EntityRegexTestClass::get[A-Z][a-zA-Z]+$/',
                'anyOf' => ['regex:/^[A-Z][a-zA-Z]+Entity$/', 'void'],
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodMustReturnType/EntityRegexTestClass.php'], [
            [
                'Method EntityRegexTestClass::getOther must have one of the return types: regex:/^[A-Z][a-zA-Z]+Entity$/, void, OtherClass given.',
                12,
            ],
            [
                'Method EntityRegexTestClass::getString must have one of the return types: regex:/^[A-Z][a-zA-Z]+Entity$/, void, string given.',
                13,
            ],
        ]);
    }
} 