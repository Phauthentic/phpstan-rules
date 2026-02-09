<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PropertyMustMatchRule>
 */
class PropertyMustMatchRuleInvalidRegexTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PropertyMustMatchRule([
            [
                'classPattern' => '/[invalid/',
                'properties' => [
                    ['name' => 'id', 'type' => 'int'],
                ],
            ],
        ]);
    }

    public function testInvalidRegexThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->analyse([__DIR__ . '/../../../data/PropertyMustMatch/TestClass.php'], []);
    }
}
