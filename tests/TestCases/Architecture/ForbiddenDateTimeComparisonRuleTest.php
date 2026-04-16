<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenDateTimeComparisonRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenDateTimeComparisonRule>
 */
class ForbiddenDateTimeComparisonRuleTest extends RuleTestCase
{
    /** Must match {@see ForbiddenDateTimeComparisonRule} output exactly for RuleTestCase. */
    private const MSG_IDENTICAL = 'Cannot compare DateTimeInterface values with ===: this compares object identity (whether both sides are the same in-memory PHP instance), not whether the two datetimes represent the same point in time. Use == / != for value comparison, or compare instants explicitly (e.g. getTimestamp(), format(), DateTimeImmutable::createFromInterface()).';

    /** @see self::MSG_IDENTICAL */
    private const MSG_NOT_IDENTICAL = 'Cannot compare DateTimeInterface values with !==: this compares object identity (whether both sides are the same in-memory PHP instance), not whether the two datetimes represent the same point in time. Use == / != for value comparison, or compare instants explicitly (e.g. getTimestamp(), format(), DateTimeImmutable::createFromInterface()).';

    protected function getRule(): Rule
    {
        return new ForbiddenDateTimeComparisonRule([]);
    }

    public function testGlobalReportsInClassMethods(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../data/ForbiddenDateTimeComparison/GlobalViolations.php'],
            [
                [self::MSG_IDENTICAL, 13],
                [self::MSG_NOT_IDENTICAL, 18],
            ]
        );
    }

    public function testGlobalReportsOutsideClassMethods(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../data/ForbiddenDateTimeComparison/GlobalFunctionViolations.php'],
            [
                [self::MSG_IDENTICAL, 11],
            ]
        );
    }
}
