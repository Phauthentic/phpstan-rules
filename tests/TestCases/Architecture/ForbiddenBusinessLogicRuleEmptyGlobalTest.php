<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenBusinessLogicRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenBusinessLogicRule>
 */
class ForbiddenBusinessLogicRuleEmptyGlobalTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbiddenBusinessLogicRule(
            [],
            [
                ['pattern' => '/^App\\\\ForbiddenBusinessLogicRule\\\\EmptyGlobalFixture::onlyIf$/', 'forbiddenStatements' => ['if']],
            ]
        );
    }

    public function testEmptyGlobalUsesPatternOverrideOnly(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenBusinessLogicRule/EmptyGlobalFixture.php'], [
            [
                'Statement "if" is not allowed in this method.',
                11,
            ],
        ]);
    }
}
