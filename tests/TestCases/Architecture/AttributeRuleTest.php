<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\AttributeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<AttributeRule>
 */
class AttributeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new AttributeRule([
            'allowed' => [
                // Controllers can only have Route attributes on the class
                [
                    'classPattern' => '/^App\\\\Controller\\\\.*/',
                    'attributes' => ['/^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/'],
                ],
                // Methods ending with Action can only have Route attributes
                [
                    'classPattern' => '/^App\\\\Controller\\\\.*/',
                    'methodPattern' => '/.*Action$/',
                    'attributes' => ['/^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/'],
                ],
                // Entity properties can only have Doctrine attributes
                [
                    'classPattern' => '/^App\\\\Entity\\\\.*/',
                    'propertyPattern' => '/.*/',
                    'attributes' => ['/^Doctrine\\\\ORM\\\\Mapping\\\\.*$/'],
                ],
            ],
            'forbidden' => [
                // Controllers cannot have Deprecated attribute on class
                [
                    'classPattern' => '/^App\\\\Controller\\\\.*/',
                    'attributes' => ['/^App\\\\Attribute\\\\Deprecated$/'],
                ],
                // Controller methods cannot have Deprecated attribute
                [
                    'classPattern' => '/^App\\\\Controller\\\\.*/',
                    'methodPattern' => '/.*Action$/',
                    'attributes' => ['/^App\\\\Attribute\\\\Deprecated$/'],
                ],
                // Entity properties cannot have Deprecated attribute
                [
                    'classPattern' => '/^App\\\\Entity\\\\.*/',
                    'propertyPattern' => '/.*/',
                    'attributes' => ['/^App\\\\Attribute\\\\Deprecated$/'],
                ],
                // Domain classes cannot have framework attributes on methods
                [
                    'classPattern' => '/^App\\\\Domain\\\\.*/',
                    'methodPattern' => '/.*/',
                    'attributes' => ['/^App\\\\Attribute\\\\FrameworkAttribute$/'],
                ],
                // Domain entities cannot have framework attributes on properties
                [
                    'classPattern' => '/^App\\\\Domain\\\\.*/',
                    'propertyPattern' => '/.*/',
                    'attributes' => ['/^App\\\\Attribute\\\\FrameworkAttribute$/'],
                ],
            ],
        ]);
    }

    public function testAllowedClassAttributes(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/AllowedClassAttributes.php'], []);
    }

    public function testForbiddenClassAttributes(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/ForbiddenClassAttributes.php'], [
            [
                'Attribute App\Attribute\Deprecated is forbidden on class App\Controller\ForbiddenClassAttributes.',
                12,
            ],
        ]);
    }

    public function testNotAllowedClassAttributes(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/NotAllowedClassAttributes.php'], [
            [
                'Attribute App\Attribute\CustomAttribute is not in the allowed list for class App\Controller\NotAllowedClassAttributes. Allowed patterns: /^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/',
                12,
            ],
        ]);
    }

    public function testMethodAttributes(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/MethodAttributes.php'], [
            [
                'Attribute App\Attribute\Deprecated is forbidden on method App\Controller\MethodAttributes::forbiddenAction.',
                27,
            ],
            [
                'Attribute App\Attribute\CustomAttribute is not in the allowed list for method App\Controller\MethodAttributes::notAllowedAction. Allowed patterns: /^Symfony\\\\Component\\\\Routing\\\\Annotation\\\\Route$/',
                35,
            ],
        ]);
    }

    public function testPropertyAttributes(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/PropertyAttributes.php'], [
            [
                'Attribute App\Attribute\Deprecated is forbidden on property App\Entity\PropertyAttributes::$forbiddenProperty.',
                27,
            ],
            [
                'Attribute App\Attribute\CustomAttribute is not in the allowed list for property App\Entity\PropertyAttributes::$notAllowedProperty. Allowed patterns: /^Doctrine\\\\ORM\\\\Mapping\\\\.*$/',
                33,
            ],
        ]);
    }

    public function testCombinedClassMethodPattern(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/CombinedClassMethodPattern.php'], [
            [
                'Attribute App\Attribute\FrameworkAttribute is forbidden on method App\Domain\Service\CombinedClassMethodPattern::processData.',
                18,
            ],
        ]);
    }

    public function testCombinedClassPropertyPattern(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/CombinedClassPropertyPattern.php'], [
            [
                'Attribute App\Attribute\FrameworkAttribute is forbidden on property App\Domain\Entity\CombinedClassPropertyPattern::$forbiddenProperty.',
                18,
            ],
        ]);
    }

    public function testNoAttributesClass(): void
    {
        $this->analyse([__DIR__ . '/../../../data/AttributeRule/NoAttributesClass.php'], []);
    }
}
