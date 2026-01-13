<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PropertyMustMatchRule>
 */
class PropertyMustMatchRuleNullableTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PropertyMustMatchRule([
            [
                'classPattern' => '/^.*Handler$/',
                'properties' => [
                    [
                        'name' => 'id',
                        'type' => 'int',
                        'visibilityScope' => 'private',
                        'nullable' => true,
                    ],
                ],
            ],
        ]);
    }

    public function testNullableFlag(): void
    {
        $this->analyse([__DIR__ . '/../../../data/PropertyMustMatch/TestClass.php'], [
            // WrongTypeAllowedHandler - wrong type entirely (string instead of int or ?int)
            [
                'Property WrongTypeAllowedHandler::$id should be of type int or ?int, string given.',
                98,
            ],
        ]);
    }
}
