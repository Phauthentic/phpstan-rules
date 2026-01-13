<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PropertyMustMatchRule>
 */
class PropertyMustMatchRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PropertyMustMatchRule([
            [
                'classPattern' => '/^.*Controller$/',
                'properties' => [
                    [
                        'name' => 'id',
                        'type' => 'int',
                        'visibilityScope' => 'private',
                        'required' => true,
                    ],
                    [
                        'name' => 'repository',
                        'type' => 'DummyRepository',
                        'visibilityScope' => 'private',
                        'required' => true,
                    ],
                ],
            ],
            [
                'classPattern' => '/^.*Service$/',
                'properties' => [
                    [
                        'name' => 'logger',
                        'type' => 'LoggerInterface',
                        'visibilityScope' => 'private',
                        'required' => false,
                    ],
                ],
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/PropertyMustMatch/TestClass.php'], [
            // MissingPropertyController - missing required property 'id'
            [
                'Class MissingPropertyController must have property $id.',
                17,
            ],

            // WrongTypeController - wrong type for 'id' (string instead of int)
            [
                'Property WrongTypeController::$id should be of type int, string given.',
                25,
            ],

            // WrongVisibilityController - wrong visibility for 'id' (public instead of private)
            [
                'Property WrongVisibilityController::$id must be private.',
                32,
            ],

            // MultipleErrorsController - wrong type and wrong visibility for 'id'
            [
                'Property MultipleErrorsController::$id should be of type int, string given.',
                39,
            ],
            [
                'Property MultipleErrorsController::$id must be private.',
                39,
            ],
            // MultipleErrorsController - wrong visibility for 'repository' (protected instead of private)
            [
                'Property MultipleErrorsController::$repository must be private.',
                40,
            ],

            // NoTypeController - missing type on 'id' property
            [
                'Property NoTypeController::$id should be of type int, none given.',
                46,
            ],

            // NullableTypeController - nullable type doesn't match expected 'int'
            [
                'Property NullableTypeController::$id should be of type int, ?int given.',
                53,
            ],

            // WrongLoggerTypeService - wrong type for optional 'logger' property
            [
                'Property WrongLoggerTypeService::$logger should be of type LoggerInterface, string given.',
                72,
            ],
        ]);
    }
}
