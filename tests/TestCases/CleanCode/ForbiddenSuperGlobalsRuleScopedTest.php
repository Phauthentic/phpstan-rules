<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\ForbiddenSuperGlobalsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenSuperGlobalsRule>
 */
class ForbiddenSuperGlobalsRuleScopedTest extends RuleTestCase
{
    private const MSG_GET = 'Use of superglobal $_GET is not allowed; inject request, session, or environment data instead of reading superglobals directly.';

    protected function getRule(): Rule
    {
        return new ForbiddenSuperGlobalsRule([
            '/^App\\\\ForbiddenSuperGlobals\\\\ScopedViolations::matchedMethod$/',
        ]);
    }

    public function testOnlyMatchedMethodReports(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../data/ForbiddenSuperGlobals/ScopedViolations.php'],
            [
                [self::MSG_GET, 11],
            ]
        );
    }

    public function testNamespacedFunctionSkippedWhenPatternsNonEmpty(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../data/ForbiddenSuperGlobals/GlobalFunctionViolations.php'],
            []
        );
    }
}
