<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenBusinessLogicRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenBusinessLogicRule>
 */
class ForbiddenBusinessLogicRuleDefaultsTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbiddenBusinessLogicRule();
    }

    public function testDefaultConstructorForbidsAllFiveWithEmptyPatterns(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenBusinessLogicRule/MinimalDefaults.php'], [
            [
                'Statement "if" is not allowed in this method.',
                11,
            ],
        ]);
    }
}
