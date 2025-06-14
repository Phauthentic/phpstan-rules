<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassnameMustMatchPatternRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassnameMustMatchPatternRule>
 */
class ClassnameMustMatchPatternRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassnameMustMatchPatternRule([
            [
                'namespace' => '/^App\\\\Service$/',
                'classPatterns' => [
                    '/Service$/',
                    '/Manager$/',
                ],
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/Service/InvalidServiceClass.php'], [
            [
                'Class App\Service\InvalidClass in namespace App\Service does not match any of the required patterns:'
                . PHP_EOL . ' - /Service$/'
                . PHP_EOL . ' - /Manager$/',
                5,
            ],
        ]);

        $this->analyse([__DIR__ . '/../../../data/Service/ValidServiceClass.php'], []);
    }
}
