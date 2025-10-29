<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\ClassMustBeReadonlyRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ClassMustBeReadonlyRuleTest>
 */
class ClassMustBeReadonlyRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ClassMustBeReadonlyRule([
            '/Controller$/', // all classes that end with "Controller"
        ]);
    }

    public function testRule(): void
    {
        // first argument: path to the example file that contains some errors that should be reported by MyRule
        // second argument: an array of expected errors,
        // each error consists of the asserted error message, and the asserted error file line
        $this->analyse([__DIR__ . '/../../../data/Controller/MissingReadonlyRuleController.php'], [
            [
                'Class App\Controller\MissingReadonlyRuleController must be readonly.', // asserted error message
                07, // asserted error line
            ],
        ]);

        // the test fails, if the expected error does not occur,
        // or if there are other errors reported beside the expected one
    }

    public function testValidReadonlyClass(): void
    {
        // Test that readonly classes don't trigger errors
        $this->analyse([__DIR__ . '/../../../data/Controller/ValidReadonlyController.php'], []);
    }
}
