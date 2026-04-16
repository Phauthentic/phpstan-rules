<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenDateTimeComparisonRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenDateTimeComparisonRule>
 */
class ForbiddenDateTimeComparisonRuleScopedTest extends RuleTestCase
{
    private const MSG_IDENTICAL = 'Cannot compare DateTimeInterface values with ===: this compares object identity (whether both sides are the same in-memory PHP instance), not whether the two datetimes represent the same point in time. Use == / != for value comparison, or compare instants explicitly (e.g. getTimestamp(), format(), DateTimeImmutable::createFromInterface()).';

    protected function getRule(): Rule
    {
        return new ForbiddenDateTimeComparisonRule([
            '/^App\\\\ForbiddenDateTimeComparison\\\\ScopedViolations::matchedMethod$/',
        ]);
    }

    public function testOnlyMatchedMethodReports(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../data/ForbiddenDateTimeComparison/ScopedViolations.php'],
            [
                [self::MSG_IDENTICAL, 13],
            ]
        );
    }

    public function testNamespacedFunctionSkippedWhenPatternsNonEmpty(): void
    {
        $this->analyse(
            [__DIR__ . '/../../../data/ForbiddenDateTimeComparison/GlobalFunctionViolations.php'],
            []
        );
    }
}
