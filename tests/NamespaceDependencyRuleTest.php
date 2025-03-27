<?php declare(strict_types = 1);

namespace App;

use Phauthentic\PhpstanRules\DependencyConstraintsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DependencyConstraintsRule>
 */
class NamespaceDependencyRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new DependencyConstraintsRule([
            '/^App\\\Domain/' => ['/^App\\\Controller\\\/']
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../data/DependencyRuleTest/Domain/Aggregate.php'], [
            [
                'Class App\Domain has a dependency on App\Controller\MissingReadonlyRuleController, which is not allowed.',
                7,
            ],
        ]);
    }
}
