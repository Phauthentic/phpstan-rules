<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\ForbiddenElseStatementsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenElseStatementsRule>
 */
class ForbiddenElseStatementsRuleClassWideTest extends RuleTestCase
{
    private const FIXTURE = __DIR__ . '/../../../data/ForbiddenElseStatementsRuleFixture.php';

    protected function getRule(): Rule
    {
        return new ForbiddenElseStatementsRule([
            '/^App\\\\ElseRules\\\\Matched::/',
        ]);
    }

    public function testClassWidePatternMatchesAllMethodsOnClass(): void
    {
        $this->analyse([self::FIXTURE], [
            [
                'Else is not allowed in App\ElseRules\Matched::matchedMethod; prefer early returns or guard clauses.',
                13,
            ],
            [
                'Else is not allowed in App\ElseRules\Matched::anotherMatched; prefer early returns or guard clauses.',
                22,
            ],
        ]);
    }
}
