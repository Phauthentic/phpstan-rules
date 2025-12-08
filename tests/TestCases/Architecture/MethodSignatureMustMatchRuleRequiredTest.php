<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodSignatureMustMatchRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodSignatureMustMatchRule>
 */
class MethodSignatureMustMatchRuleRequiredTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodSignatureMustMatchRule([
            [
                'pattern' => '/^.*TestController::execute$/',
                'minParameters' => 1,
                'maxParameters' => 1,
                'signature' => [
                    ['type' => 'int', 'pattern' => '/^id$/'],
                ],
                'visibilityScope' => 'public',
                'required' => true,
            ],
        ]);
    }

    public function testRequiredMethodRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodSignatureMustMatch/RequiredMethodTestClass.php'], [
            // MyTestController is missing the required execute method
            [
                'Class MyTestController must implement method execute with signature: public function execute(int $param1).',
                8,
            ],
            // AnotherTestController implements the method correctly - no error expected

            // YetAnotherTestController is missing the required execute method
            [
                'Class YetAnotherTestController must implement method execute with signature: public function execute(int $param1).',
                24,
            ],
            // NotAController doesn't match the pattern - no error expected
        ]);
    }
}
