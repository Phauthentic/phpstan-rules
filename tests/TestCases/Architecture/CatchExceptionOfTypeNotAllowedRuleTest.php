<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\TestCases\Architecture;

use Phauthentic\PHPStanRules\Architecture\CatchExceptionOfTypeNotAllowedRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CatchExceptionOfTypeNotAllowedRule>
 */
class CatchExceptionOfTypeNotAllowedRuleTest extends RuleTestCase
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new CatchExceptionOfTypeNotAllowedRule([
            'Exception',
            'Error',
            'Throwable',
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../../data/CatchExceptionOfTypeNotAllowed/CatchForbiddenException.php'], [
            [
                'Catching exception of type Exception is not allowed.',
                13,
            ],
            [
                'Catching exception of type Error is not allowed.',
                19,
            ],
            [
                'Catching exception of type Throwable is not allowed.',
                25,
            ],
        ]);
    }

    public function testAllowedExceptions(): void
    {
        $this->analyse([__DIR__ . '/../../../data/CatchExceptionOfTypeNotAllowed/CatchAllowedException.php'], []);
    }
}
