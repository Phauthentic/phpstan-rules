<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenAccessorsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenAccessorsRule>
 */
class ForbiddenAccessorsRuleProtectedVisibilityTest extends RuleTestCase
{
    protected function getRule(): ForbiddenAccessorsRule
    {
        return new ForbiddenAccessorsRule(
            classPatterns: ['/\\\\Domain\\\\.*Entity$/'],
            forbidGetters: true,
            forbidSetters: true,
            visibility: ['public', 'protected']
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
                'Class App\Domain\UserEntity must not have a public setter method setName().',
                16,
            ],
            [
                'Class App\Domain\UserEntity must not have a public getter method getAge().',
                21,
            ],
            [
                'Class App\Domain\UserEntity must not have a public setter method setAge().',
                26,
            ],
            [
                'Class App\Domain\UserEntity must not have a protected getter method getActive().',
                31,
            ],
            [
                'Class App\Domain\UserEntity must not have a protected setter method setActive().',
                36,
            ],
        ]);
    }
}
