<?php declare(strict_types = 1);

namespace App;

use Phauthentic\PhpstanRules\FinalClassRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<FinalClassRule>
 */
class FinalClassRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new FinalClassRule([
            '/Service$/', // all classes that end with "Service"
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../data/Service/MissingFinalRuleService.php'], [
            [
                'Class App\Service\MissingFinalRuleService must be final.',
                5,
            ],
        ]);
    }
}
