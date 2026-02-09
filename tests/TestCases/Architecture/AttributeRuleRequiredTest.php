<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\AttributeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Tests for the required attribute functionality.
 *
 * @extends RuleTestCase<AttributeRule>
 */
class AttributeRuleRequiredTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new AttributeRule([
            'required' => [
                // Controllers must have Route attribute on the class
                [
                    'classPattern' => '/^App\\\\Controller\\\\.*/',
                    'attributes' => ['/^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/'],
                ],
                // Methods ending with Action must have Route attribute
                [
                    'classPattern' => '/^App\\\\Controller\\\\.*/',
                    'methodPattern' => '/.*Action$/',
                    'attributes' => ['/^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/'],
                ],
                // Entity properties must have a Doctrine Column attribute
                [
                    'classPattern' => '/^App\\\\Entity\\\\.*/',
                    'propertyPattern' => '/.*/',
                    'attributes' => ['/^Doctrine\\\\ORM\\\\Mapping\\\\Column$/'],
                ],
            ],
        ]);
    }

    public function testRequiredClassAttributeMissing(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/RequiredClassAttribute.php'], [
            [
                'Missing required attribute matching pattern /^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/ on class App\\Controller\\RequiredClassAttribute.',
                10,
            ],
        ]);
    }

    public function testRequiredClassAttributePresent(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/RequiredClassAttributePresent.php'], []);
    }

    public function testRequiredMethodAttribute(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/RequiredMethodAttribute.php'], [
            [
                'Missing required attribute matching pattern /^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/ on class App\\Controller\\RequiredMethodAttribute.',
                12,
            ],
            [
                'Missing required attribute matching pattern /^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/ on method App\\Controller\\RequiredMethodAttribute::missingRequiredAction.',
                25,
            ],
        ]);
    }

    public function testRequiredPropertyAttribute(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/RequiredPropertyAttribute.php'], [
            [
                'Missing required attribute matching pattern /^Doctrine\\\\ORM\\\\Mapping\\\\Column$/ on property App\\Entity\\RequiredPropertyAttribute::$missingRequired.',
                23,
            ],
        ]);
    }
}
