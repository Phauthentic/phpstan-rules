<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenAccessorsRule>
 */
class ForbiddenAccessorsRuleGettersOnlyTest extends RuleTestCase
{
    protected function getRule(): ForbiddenAccessorsRule
    {
        return new ForbiddenAccessorsRule(
            classPatterns: ['/\\\\Domain\\\\.*Entity$/'],
            forbidGetters: true,
            forbidSetters: false,
            visibility: ['public']
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenAccessors/EntityWithAccessors.php'], [
            [
                'Class App\Domain\UserEntity must not have a public getter method getName().',
                11,
            ],
            [
                'Class App\Domain\UserEntity must not have a public getter method getAge().',
                21,
            ],
        ]);
    }
}
