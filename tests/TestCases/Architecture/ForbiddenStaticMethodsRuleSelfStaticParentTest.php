<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenStaticMethodsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenStaticMethodsRule>
 */
class ForbiddenStaticMethodsRuleSelfStaticParentTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbiddenStaticMethodsRule([
            '/^App\\\\Forbidden\\\\ForbiddenService::.*/',
        ]);
    }

    public function testSelfAndStaticCalls(): void
    {
        $this->analyse([__DIR__ . '/../../../data/Forbidden/ForbiddenService.php'], [
            [
                'Static method call "App\Forbidden\ForbiddenService::create" is forbidden.',
                16,
            ],
            [
                'Static method call "App\Forbidden\ForbiddenService::create" is forbidden.',
                21,
            ],
        ]);
    }

    public function testParentCall(): void
    {
        $this->analyse([__DIR__ . '/../../../data/Forbidden/ChildService.php'], [
            [
                'Static method call "App\Forbidden\ForbiddenService::create" is forbidden.',
                11,
            ],
        ]);
    }
}
