<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenAccessorsRule>
 */
class ForbiddenAccessorsRuleSettersOnlyTest extends RuleTestCase
{
    protected function getRule(): ForbiddenAccessorsRule
    {
        return new ForbiddenAccessorsRule(
            classPatterns: ['/\\\\Domain\\\\.*Entity$/'],
            forbidGetters: false,
            forbidSetters: true,
            visibility: ['public']
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenAccessors/EntityWithAccessors.php'], [
            [
                'Class App\Domain\UserEntity must not have a public setter method setName().',
                16,
            ],
            [
                'Class App\Domain\UserEntity must not have a public setter method setAge().',
                26,
            ],
        ]);
    }
}
