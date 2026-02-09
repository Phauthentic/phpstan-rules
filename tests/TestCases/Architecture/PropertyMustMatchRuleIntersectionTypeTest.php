<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<PropertyMustMatchRule>
 */
class PropertyMustMatchRuleIntersectionTypeTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new PropertyMustMatchRule([
            [
                'classPattern' => '/^.*Command$/',
                'properties' => [
                    [
                        'name' => 'collection',
                        'type' => 'Countable&Iterator',
                        'visibilityScope' => 'private',
                    ],
                ],
            ],
        ]);
    }

    public function testIntersectionTypePropertyMatches(): void
    {
        $this->analyse([__DIR__ . '/../../../data/PropertyMustMatch/TestClass.php'], []);
    }
}
