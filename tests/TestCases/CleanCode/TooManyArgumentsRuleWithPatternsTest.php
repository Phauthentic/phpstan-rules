<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\CleanCode;

use Phauthentic\PHPStanRules\CleanCode\TooManyArgumentsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TooManyArgumentsRule>
 */
class TooManyArgumentsRuleWithPatternsTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Only apply to classes ending with "Service"
        return new TooManyArgumentsRule(3, ['/Service$/']);
    }

    public function testRuleWithPatterns(): void
    {
        $this->analyse([__DIR__ . '/../../../data/TooManyArgumentsClass.php'], [
            [
                'Method App\Service\TooManyArgsService::methodWithTooManyArguments has too many arguments (5). Maximum allowed is 3.',
                22,
            ],
            // TooManyArgumentsClass and TooManyArgsOther should NOT trigger errors because patterns don't match
        ]);
    }
}

