<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PropertyMustMatchRule>
 */
class PropertyMustMatchRuleInvalidVisibilityTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PropertyMustMatchRule([
            [
                'classPattern' => '/^.*Controller$/',
                'properties' => [
                    [
                        'name' => 'id',
                        'visibilityScope' => 'invalid',
                    ],
                ],
            ],
        ]);
    }

    public function testInvalidVisibilityScopeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid visibilityScope "invalid"');
        $this->analyse([__DIR__ . '/../../../data/PropertyMustMatch/TestClass.php'], []);
    }
}
