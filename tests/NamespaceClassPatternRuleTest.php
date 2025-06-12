<?php declare(strict_types = 1);

namespace App;

use Phauthentic\PhpstanRules\NamespaceClassPatternRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NamespaceClassPatternRule>
 */
class NamespaceClassPatternRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new NamespaceClassPatternRule([
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
        $this->analyse([__DIR__ . '/../data/Service/InvalidServiceClass.php'], [
            [
                'Class App\Service\InvalidClass in namespace App\Service does not match any of the required patterns.',
                5,
            ],
        ]);

        $this->analyse([__DIR__ . '/../data/Service/ValidServiceClass.php'], []);
    }
}
