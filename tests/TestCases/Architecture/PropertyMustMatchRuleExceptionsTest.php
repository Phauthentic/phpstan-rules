<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\PropertyMustMatchRule;
use PHPUnit\Framework\TestCase;

class PropertyMustMatchRuleExceptionsTest extends TestCase
{
    public function testEmptyPropertyPatternsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one property pattern must be provided.');
        new PropertyMustMatchRule([]);
    }
}
