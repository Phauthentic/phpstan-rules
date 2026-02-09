<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ForbiddenStaticMethodsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbiddenStaticMethodsRule>
 */
class ForbiddenStaticMethodsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbiddenStaticMethodsRule([
            // Namespace-level: forbid all static calls to classes in App\Legacy
            '/^App\\\\Legacy\\\\.*::.*/',
            // Class-level: forbid all static calls on App\Utils\StaticHelper
            '/^App\\\\Utils\\\\StaticHelper::.*/',
            // Method-level: forbid only DateTime::createFromFormat
            '/^DateTime::createFromFormat$/',
        ]);
    }

    public function testForbiddenNamespaceStaticCall(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenStaticMethods/ForbiddenNamespaceStaticCall.php'], [
            [
                'Static method call "App\Legacy\LegacyHelper::doSomething" is forbidden.',
                17,
            ],
        ]);
    }

    public function testForbiddenClassStaticCall(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenStaticMethods/ForbiddenClassStaticCall.php'], [
            [
                'Static method call "App\Utils\StaticHelper::calculate" is forbidden.',
                17,
            ],
        ]);
    }

    public function testForbiddenMethodStaticCall(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenStaticMethods/ForbiddenMethodStaticCall.php'], [
            [
                'Static method call "DateTime::createFromFormat" is forbidden.',
                17,
            ],
        ]);
    }

    public function testAllowedStaticCall(): void
    {
        $this->analyse([__DIR__ . '/../../../data/ForbiddenStaticMethods/AllowedStaticCall.php'], []);
    }

    public function testAllowedMethodOnPartiallyForbiddenClass(): void
    {
        // DateTime::getLastErrors is allowed, only createFromFormat is forbidden
        $this->analyse([__DIR__ . '/../../../data/ForbiddenStaticMethods/AllowedMethodOnForbiddenClass.php'], []);
    }
}
