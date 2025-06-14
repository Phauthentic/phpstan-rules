<?php

declare(strict_types=1);

namespace App;

use Phauthentic\PhpstanRules\DependencyConstraintsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DependencyConstraintsRule>
 */
class NamespaceDependencyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DependencyConstraintsRule([
            '/^App\\\\Domain(?:\\\\\\w+)*$/' => ['/^App\\\Controller\\\/']
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../data/DependencyRuleTest/Domain/Aggregate.php'], [
            [
                'Dependency violation: A class in namespace `App\Domain` is not allowed to depend on `App\Controller\MissingReadonlyRuleController`.',
                7,
            ],
        ]);
    }
}
