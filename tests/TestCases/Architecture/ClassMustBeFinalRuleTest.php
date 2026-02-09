<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustBeFinalRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustBeFinalRule>
 */
class ClassMustBeFinalRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustBeFinalRule([
            '/Service$/', // all classes that end with "Service"
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/Service/MissingFinalRuleService.php'], [
            [
                'Class App\Service\MissingFinalRuleService must be final.',
                5,
            ],
        ]);
    }

    public function testRuleIgnoresAbstractClassesByDefault(): void
    {
        $this->analyse([__DIR__ . '/../../../data/Service/AbstractServiceClass.php'], []);
    }

    public function testFinalClassMatchingPatternPassesRule(): void
    {
        // A class that matches the pattern but is already final should produce no errors
        $this->analyse([__DIR__ . '/../../../data/Service/FinalRuleService.php'], []);
    }

    public function testAnonymousClassIsSkipped(): void
    {
        // Anonymous classes should be skipped (no class name to match)
        $this->analyse([__DIR__ . '/../../../data/Service/AnonymousServiceClass.php'], []);
    }
}
