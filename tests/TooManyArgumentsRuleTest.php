<?php

declare(strict_types=1);

namespace App;

use Phauthentic\PhpstanRules\TooManyArgumentsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TooManyArgumentsRule>
 */
class TooManyArgumentsRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new TooManyArgumentsRule(3); // Set the maximum number of allowed arguments
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../data/TooManyArgumentsClass.php'], [
            [
                'Method App\TooManyArgumentsClass::methodWithTooManyArguments has too many arguments (4). Maximum allowed is 3.',
                7,
            ],
        ]);
    }
}
