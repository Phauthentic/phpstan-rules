<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenDependenciesRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Test allowedDependencies feature that overrides forbidden dependencies
 *
 * @extends RuleTestCase<ForbiddenDependenciesRule>
 */
class ForbiddenDependenciesAllowedTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbiddenDependenciesRule(
            // Forbid everything (any namespaced class)
            [
                '/^App\\\\Capability\\\\.*\\\\Domain$/' => [
                    '/.*\\\\.*/'  // Match anything with a backslash (namespaced)
                ]
            ],
            true, // Enable FQCN checking
            [     // All reference types (default)
                'new',
                'param',
                'return',
                'property',
                'static_call',
                'static_property',
                'class_const',
                'instanceof',
                'catch',
                'extends',
                'implements',
            ],
            // Allow specific namespaces as override
            [
                '/^App\\\\Capability\\\\.*\\\\Domain$/' => [
                    '/^App\\\\Shared\\\\/',
                    '/^App\\\\Capability\\\\/',
                    '/^Psr\\\\/'
                ]
            ]
        );
    }

    /**
     * Test that allowed dependencies do not trigger errors
     */
    public function testAllowedDependenciesOverrideForbidden(): void
    {
        // Should have no errors - all dependencies match allowed patterns
        $this->analyse([__DIR__ . '/../../../data/ForbiddenDependenciesAllowed/AllowedOverride.php'], []);
    }

    /**
     * Test that forbidden dependencies not in allowed list still trigger errors
     */
    public function testForbiddenDependenciesStillReportErrors(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenDependenciesAllowed/StillForbidden.php'], [
            [
                'Forbidden dependency: A class in namespace `App\Capability\Billing\Domain` is not allowed to depend on `Doctrine\ORM\EntityManager`.',
                8,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability\Billing\Domain` is not allowed to depend on `Symfony\Component\HttpFoundation\Request`.',
                9,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability\Billing\Domain` is not allowed to depend on `Doctrine\ORM\EntityManager`.',
                17,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability\Billing\Domain` is not allowed to depend on `Doctrine\ORM\EntityManager`.',
                19,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability\Billing\Domain` is not allowed to depend on `Symfony\Component\HttpFoundation\Request`.',
                24,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability\Billing\Domain` is not allowed to depend on `Doctrine\ORM\EntityManager`.',
                29,
            ],
            [
                'Forbidden dependency: A class in namespace `App\Capability\Billing\Domain` is not allowed to depend on `Doctrine\ORM\EntityManager`.',
                31,
            ],
        ]);
    }
}
