<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustBeFinalRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustBeFinalRule>
 */
class ClassMustBeFinalRuleWithAbstractClassesTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustBeFinalRule([
            '/ServiceClass$/', // all classes that end with "ServiceClass"
        ], false); // Don't ignore abstract classes
    }

    public function testRuleWithAbstractClassesNotIgnored(): void
    {
        $this->analyse([__DIR__ . '/../../../data/Service/AbstractServiceClass.php'], [
            [
                'Class App\Service\AbstractServiceClass must be final.',
                5,
            ],
        ]);
    }


}
