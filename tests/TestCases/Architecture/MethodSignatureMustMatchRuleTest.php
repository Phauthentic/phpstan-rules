<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodSignatureMustMatchRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodSignatureMustMatchRule>
 */
class MethodSignatureMustMatchRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodSignatureMustMatchRule([
            [
                'pattern' => '/^TestClass::testMethod$/',
                'minParameters' => 2,
                'maxParameters' => 3,
                'signature' => [
                    ['type' => 'int', 'pattern' => '/^a/'],
                    ['type' => 'string', 'pattern' => '/^b/'],
                ],
                'visibilityScope' => 'private',
            ],
            [
                'pattern' => '/^TestClass::testMethodNoType$/',
                'minParameters' => 1,
                'maxParameters' => 2,
                'signature' => [
                    ['pattern' => '/^x$/'], // No type specified
                    ['type' => 'string', 'pattern' => '/^y$/'], // Type specified
                ],
                'visibilityScope' => 'public',
            ],
            [
                'pattern' => '/^TestClass::testMethodWithWrongType$/',
                'minParameters' => 2,
                'maxParameters' => 2,
                'signature' => [
                    ['type' => 'string', 'pattern' => '/^x$/'], // Type specified but wrong
                    ['pattern' => '/^y$/'], // No type specified, should be ignored
                ],
                'visibilityScope' => 'public',
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodSignatureMustMatch/TestClass.php'], [
            // Errors for testMethod (type checking enabled)
            [
                'Method TestClass::testMethod has 1 parameters, but at least 2 required.',
                5,
            ],
            [
                'Method TestClass::testMethod is missing parameter #2 of type string.',
                5,
            ],
            [
                'Method TestClass::testMethod must be private.',
                5,
            ],
            // No errors for testMethodNoType since:
            // - First parameter has no type specified, so type checking is skipped
            // - Second parameter has correct type (string) and name matches pattern
            // - Parameter count is within limits (1-2)
            // - Visibility is correct (public)
            
            // Error for testMethodWithWrongType:
            // - First parameter has wrong type (int instead of string)
            // - Second parameter has no type specified, so type checking is skipped
            [
                'Method TestClass::testMethodWithWrongType parameter #1 should be of type string, int given.',
                13,
            ],
        ]);
    }
}
