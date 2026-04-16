<?php

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author    Florian Krämer
 * @link      https://github.com/Phauthentic
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\CleanCode;

use PhpParser\Node;
use PhpParser\Node\Stmt\Else_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Specification:
 *
 * - Forbids plain `else` (not `elseif`) inside class methods when the enclosing
 *   `Full\Class\Name::methodName` string matches any configured regex, using the
 *   same convention as MethodMustReturnTypeRule.
 * - If `patterns` is empty, the rule does nothing.
 * - If the scope has no class or no enclosing function, the rule does nothing.
 *
 * @implements Rule<Else_>
 */
class ForbiddenElseStatementsRule implements Rule
{
    private const ERROR_MESSAGE = 'Else is not allowed in %s; prefer early returns or guard clauses.';

    private const IDENTIFIER = 'phauthentic.cleancode.forbiddenElseStatements';

    /**
     * @param string[] $patterns Regex patterns matched against `Full\Class\Name::methodName`
     */
    public function __construct(
        private array $patterns = [],
    ) {
    }

    public function getNodeType(): string
    {
        return Else_::class;
    }

    /**
     * @param Else_ $node
     * @return list<RuleError>
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->patterns === []) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        $function = $scope->getFunction();
        if ($classReflection === null || $function === null) {
            return [];
        }

        $fullName = $classReflection->getName() . '::' . $function->getName();
        if (!$this->matchesAnyPattern($fullName)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $fullName))
                ->identifier(self::IDENTIFIER)
                ->line($node->getLine())
                ->build(),
        ];
    }

    private function matchesAnyPattern(string $fullName): bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $fullName) === 1) {
                return true;
            }
        }

        return false;
    }
}
