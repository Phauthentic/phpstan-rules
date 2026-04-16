<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\ForbiddenElseStatementsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenElseStatementsRule>
 */
class ForbiddenElseStatementsRuleNoMatchTest extends RuleTestCase
{
    private const FIXTURE = __DIR__ . '/../../../data/ForbiddenElseStatementsRuleFixture.php';

    protected function getRule(): Rule
    {
        return new ForbiddenElseStatementsRule([
            '/^App\\\\ElseRules\\\\DoesNotExist::/',
        ]);
    }

    public function testNoReportWhenPatternDoesNotMatch(): void
    {
        $this->analyse([self::FIXTURE], []);
    }
}
