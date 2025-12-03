<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenDependenciesRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test FQCN detection with all reference types enabled
 *
 * @extends RuleTestCase<ForbiddenDependenciesRule>
 */
class DependencyConstraintsRuleFqcnTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbiddenDependenciesRule(
            [
                '/^App\\\\Capability(?:\\\\\\w+)*$/' => [
                    '/^DateTime$/',
                    '/^DateTimeImmutable$/',
                    '/^DateTimeInterface$/',
                    '/^DateTimeZone$/',
                    '/^DateInterval$/',
                ]
            ],
            true // Enable FQCN checking with all reference types (default)
        );
    }

    /**
     * Test new instantiation detection
     */
    public function testNewInstantiation(): void
    {
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/NewInstantiation.php'], [
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                12,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                18,
            ],
        ]);
    }

    /**
     * Test type hints (property, param, return) detection
     */
    public function testTypeHints(): void
    {
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/TypeHints.php'], [
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                10,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                12,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                17,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                23,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                29,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                35,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                46,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                52,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                52,
            ],
        ]);
    }

    /**
     * Test static calls, properties, and class constants detection
     */
    public function testStaticReferences(): void
    {
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/StaticCalls.php'], [
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                12,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                18,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                24,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                30,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                36,
            ],
        ]);
    }

    /**
     * Test instanceof and catch block detection
     */
    public function testInstanceofAndCatch(): void
    {
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/InstanceofAndCatch.php'], [
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                12,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                18,
            ],
        ]);
    }

    /**
     * Test extends and implements detection
     */
    public function testExtendsAndImplements(): void
    {
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/ExtendsAndImplements.php'], [
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                8,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeInterface`.',
                13,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeZone`.',
                20,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeInterface`.',
                35,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateInterval`.',
                35,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateInterval`.',
                37,
            ],
        ]);
    }

    /**
     * Test that all reference types work together
     */
    public function testAllReferenceTypes(): void
    {
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/SelectiveReferenceTypes.php'], [
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                10,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                13,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                19,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                27,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                33,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                39,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                45,
            ],
        ]);
    }

    /**
     * Test that both use statements and FQCN are caught when enabled
     * Note: PHPStan resolves imported names to FQCN, so both explicit FQCN
     * and imported class usages are caught by FQCN checking
     */
    public function testMixedUsageDetection(): void
    {
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/MixedUsageForbidden.php'], [
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                10,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                11,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                16,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                18,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTime`.',
                24,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                28,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                30,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                34,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability` is not allowed to depend on `DateTimeImmutable`.',
                36,
            ],
        ]);
    }

    /**
     * Test that allowed classes are not caught
     */
    public function testAllowedClasses(): void
    {
        // Should have no errors for allowed classes
        $this->analyse([__DIR__ . '/../../../data/DependencyConstraintsRuleFqcn/MixedUsageAllowed.php'], []);
    }
}
