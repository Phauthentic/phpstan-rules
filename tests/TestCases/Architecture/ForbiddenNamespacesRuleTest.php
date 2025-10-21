<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenNamespacesRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenNamespacesRule>
 */
class ForbiddenNamespacesRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ForbiddenNamespacesRule([
            '/^App\\\\Legacy\\\\.*/',
            '/^App\\\\Deprecated\\\\.*/',
        ]);
    }

    public function testRule(): void
    {
        // Test a file with a forbidden namespace
        $this->analyse([__DIR__ . '/../../../data/ForbiddenNamespace/ForbiddenLegacyClass.php'], [
            [
                'Namespace "App\Legacy\OldCode" is forbidden and cannot be declared.',
                3,
            ],
        ]);

        // Test a file with another forbidden namespace
        $this->analyse([__DIR__ . '/../../../data/ForbiddenNamespace/ForbiddenDeprecatedClass.php'], [
            [
                'Namespace "App\Deprecated\Utils" is forbidden and cannot be declared.',
                3,
            ],
        ]);

        // Test a file with an allowed namespace - should have no errors
        $this->analyse([__DIR__ . '/../../../data/ForbiddenNamespace/AllowedNamespaceClass.php'], []);
    }
}
