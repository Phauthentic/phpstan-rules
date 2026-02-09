<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule;
use PHPUnit\Framework\TestCase;

class ForbiddenAccessorsRuleExceptionsTest extends TestCase
{
    public function testEmptyClassPatternsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one class pattern must be provided.');
        new ForbiddenAccessorsRule(classPatterns: []);
    }

    public function testInvalidVisibilityThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid visibility value(s): invalid');
        new ForbiddenAccessorsRule(classPatterns: ['/./'], visibility: ['invalid']);
    }
}
