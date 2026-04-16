<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenBusinessLogicRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenBusinessLogicRule>
 */
class ForbiddenBusinessLogicRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbiddenBusinessLogicRule(
            ['if', 'for', 'foreach', 'while', 'switch'],
            [
                ['pattern' => '/^App\\\\ForbiddenBusinessLogicRule\\\\ScenarioFixture::onlyIf$/', 'forbiddenStatements' => ['if']],
                '/::noOverrideX$/',
                ['pattern' => '/::unknownNamesN$/', 'forbiddenStatements' => ['bogus', 'if']],
                ['pattern' => '/^App\\\\ForbiddenBusinessLogicRule\\\\ScenarioFixture::lastWins/', 'forbiddenStatements' => ['if', 'for']],
                ['pattern' => '/^App\\\\ForbiddenBusinessLogicRule\\\\ScenarioFixture::lastWinsM$/', 'forbiddenStatements' => ['switch']],
            ]
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenBusinessLogicRule/ScenarioFixture.php'], [
            [
                'Statement "if" is not allowed in this method.',
                14,
            ],
            [
                'Statement "if" is not allowed in this method.',
                20,
            ],
            [
                'Statement "if" is not allowed in this method.',
                28,
            ],
            [
                'Statement "switch" is not allowed in this method.',
                38,
            ],
            [
                'Statement "if" is not allowed in this method.',
                46,
            ],
        ]);
    }
}
