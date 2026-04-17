<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\ForbiddenSuperGlobalsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenSuperGlobalsRule>
 */
class ForbiddenSuperGlobalsRuleTest extends RuleTestCase
{
    /** Must match {@see ForbiddenSuperGlobalsRule} output exactly for RuleTestCase. */
    private const MSG_GET = 'Use of superglobal $_GET is not allowed; inject request, session, or environment data instead of reading superglobals directly.';

    private const MSG_POST = 'Use of superglobal $_POST is not allowed; inject request, session, or environment data instead of reading superglobals directly.';

    private const MSG_SERVER = 'Use of superglobal $_SERVER is not allowed; inject request, session, or environment data instead of reading superglobals directly.';

    protected function getRule(): Rule
    {
        return new ForbiddenSuperGlobalsRule([]);
    }

    public function testGlobalReportsInClassMethods(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../data/ForbiddenSuperGlobals/GlobalViolations.php'],
            [
                [self::MSG_GET, 11],
                [self::MSG_POST, 16],
            ]
        );
    }

    public function testGlobalReportsOutsideClassMethods(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../data/ForbiddenSuperGlobals/GlobalFunctionViolations.php'],
            [
                [self::MSG_SERVER, 9],
            ]
        );
    }
}
