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
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/MethodSignatureMustMatch/TestClass.php'], [
            [
                'Method TestClass::testMethod has 1 parameters, but at least 2 required.',
                5,
            ],
            [
                'Method TestClass::testMethod is missing parameter #2 of type string.',
                5,
            ],
        ]);
    }
}