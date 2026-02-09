<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\MethodsReturningBoolMustFollowNamingConventionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MethodsReturningBoolMustFollowNamingConventionRule>
 */
class MethodsReturningBoolMustFollowNamingConventionEdgeCasesTest extends RuleTestCase
{
    protected function getRule(): MethodsReturningBoolMustFollowNamingConventionRule
    {
        return new MethodsReturningBoolMustFollowNamingConventionRule();
    }

    public function testMagicMethodsAndNoReturnTypeAreSkipped(): void
    {
        $this->analyse([__DIR__ . '/../../../data/BoolNaming/EdgeCaseMethodBoolClass.php'], [
            [
                'Method App\BoolNaming\EdgeCaseMethodBoolClass::check() returns boolean but does not follow naming convention (regex: /^(is|has|can|should|was|will)[A-Z_]/).',
                26,
            ],
        ]);
    }
}
