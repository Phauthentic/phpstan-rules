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
                        'type' => 'App\PropertyMustMatch\DummyRepository',
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
                        'type' => 'App\PropertyMustMatch\LoggerInterface',
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
                'Class App\PropertyMustMatch\MissingPropertyController must have property $id.',
                19,
            ],

            // WrongTypeController - wrong type for 'id' (string instead of int)
            [
                'Property App\PropertyMustMatch\WrongTypeController::$id should be of type int, string given.',
                27,
            ],

            // WrongVisibilityController - wrong visibility for 'id' (public instead of private)
            [
                'Property App\PropertyMustMatch\WrongVisibilityController::$id must be private.',
                34,
            ],

            // MultipleErrorsController - wrong type and wrong visibility for 'id'
            [
                'Property App\PropertyMustMatch\MultipleErrorsController::$id should be of type int, string given.',
                41,
            ],
            [
                'Property App\PropertyMustMatch\MultipleErrorsController::$id must be private.',
                41,
            ],
            // MultipleErrorsController - wrong visibility for 'repository' (protected instead of private)
            [
                'Property App\PropertyMustMatch\MultipleErrorsController::$repository must be private.',
                42,
            ],

            // NoTypeController - missing type on 'id' property
            [
                'Property App\PropertyMustMatch\NoTypeController::$id should be of type int, none given.',
                48,
            ],

            // NullableTypeController - nullable type doesn't match expected 'int'
            [
                'Property App\PropertyMustMatch\NullableTypeController::$id should be of type int, ?int given.',
                55,
            ],

            // WrongLoggerTypeService - wrong type for optional 'logger' property
            [
                'Property App\PropertyMustMatch\WrongLoggerTypeService::$logger should be of type App\PropertyMustMatch\LoggerInterface, string given.',
                74,
            ],
        ]);
    }
}
