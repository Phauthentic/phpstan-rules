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
            [
                'pattern' => '/^TestClass::testMaxParams$/',
                'minParameters' => 1,
                'maxParameters' => 2,
                'signature' => [],
                'visibilityScope' => 'public',
            ],
            [
                'pattern' => '/^TestClass::testNameMismatch$/',
                'minParameters' => 2,
                'maxParameters' => 2,
                'signature' => [
                    ['type' => 'int', 'pattern' => '/^param/'], // Name pattern doesn't match
                    ['type' => 'string', 'pattern' => '/^param/'], // Name pattern doesn't match
                ],
                'visibilityScope' => 'public',
            ],
            [
                'pattern' => '/^TestClass::testNullableTypes$/',
                'minParameters' => 2,
                'maxParameters' => 2,
                'signature' => [
                    ['type' => '?int', 'pattern' => '/^nullable/'],
                    ['type' => '?string', 'pattern' => '/^nullable/'],
                ],
                'visibilityScope' => 'public',
            ],
            [
                'pattern' => '/^TestClass::testClassTypes$/',
                'minParameters' => 2,
                'maxParameters' => 2,
                'signature' => [
                    ['type' => 'DummyClass', 'pattern' => '/^dummy/'],
                    ['type' => 'string', 'pattern' => '/^name/'],
                ],
                'visibilityScope' => 'public',
            ],
            [
                'pattern' => '/^TestClass::testProtectedMethod$/',
                'minParameters' => 1,
                'maxParameters' => 1,
                'signature' => [
                    ['type' => 'int', 'pattern' => '/^value/'],
                ],
                'visibilityScope' => 'protected',
            ],
            [
                'pattern' => '/^TestClass::testNoVisibilityReq$/',
                'minParameters' => 1,
                'maxParameters' => 1,
                'signature' => [
                    ['type' => 'int', 'pattern' => '/^x/'],
                ],
                // No visibilityScope specified
            ],
            [
                'pattern' => '/^TestClass::testValidMethod$/',
                'minParameters' => 2,
                'maxParameters' => 2,
                'signature' => [
                    ['type' => 'int', 'pattern' => '/^alpha/'],
                    ['type' => 'string', 'pattern' => '/^beta/'],
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
                9,
            ],
            [
                'Method TestClass::testMethod is missing parameter #2 of type string.',
                9,
            ],
            [
                'Method TestClass::testMethod must be private.',
                9,
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
                17,
            ],
            
            // Errors for testMaxParams - exceeds max parameters
            [
                'Method TestClass::testMaxParams has 4 parameters, but at most 2 allowed.',
                22,
            ],
            
            // Errors for testNameMismatch - parameter names don't match patterns
            [
                'Method TestClass::testNameMismatch parameter #1 name "wrongName" does not match pattern /^param/.',
                27,
            ],
            [
                'Method TestClass::testNameMismatch parameter #2 name "anotherWrong" does not match pattern /^param/.',
                27,
            ],
            
            // No errors for testNullableTypes - nullable types should match correctly
            
            // No errors for testClassTypes - class types should match correctly
            
            // No errors for testProtectedMethod - protected visibility matches
            
            // No errors for testNoVisibilityReq - no visibility requirement specified
            
            // No errors for testValidMethod - everything matches correctly
        ]);
    }
}
