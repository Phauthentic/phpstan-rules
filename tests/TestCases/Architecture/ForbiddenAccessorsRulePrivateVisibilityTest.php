<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenAccessorsRule>
 */
class ForbiddenAccessorsRulePrivateVisibilityTest extends RuleTestCase
{
    protected function getRule(): ForbiddenAccessorsRule
    {
        return new ForbiddenAccessorsRule(
            classPatterns: ['/\\\\Domain\\\\.*Entity$/'],
            forbidGetters: true,
            forbidSetters: true,
            visibility: ['private']
        );
    }

    public function testPrivateAccessorsAreDetected(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenAccessors/EntityWithAccessors.php'], [
            [
                'Class App\Domain\UserEntity must not have a private getter method getPrivateValue().',
                41,
            ],
            [
                'Class App\Domain\UserEntity must not have a private setter method setPrivateValue().',
                46,
            ],
        ]);
    }
}
