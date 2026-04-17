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

use Phauthentic\PHPStanRules\Architecture\ClassNameResolver;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 *
 * - Reports uses of PHP superglobals (`$GLOBALS`, `$_GET`, `$_POST`, etc.) as simple variables
 *   (dynamic variable names are ignored).
 * - If `patterns` is empty, the rule applies globally (any scope PHPStan analyzes).
 * - If `patterns` is non-empty, only class methods whose `Full\Class\Name::methodName` matches
 *   any pattern are checked (requires class and function reflections in scope).
 *
 * Unlike {@see ForbiddenElseStatementsRule}, an empty `patterns` list does not disable this rule;
 * it means “no scope filter” (global).
 *
 * @implements Rule<Variable>
 */
class ForbiddenSuperGlobalsRule implements Rule
{
    use ClassNameResolver;

    private const IDENTIFIER = 'phauthentic.cleancode.forbiddenSuperGlobals';

    private const ERROR_MESSAGE = 'Use of superglobal %s is not allowed; inject request, session, or environment data instead of reading superglobals directly.';

    /**
     * Parser variable names for PHP superglobals (no leading `$`).
     *
     * @var list<string>
     */
    private const SUPERGLOBAL_NAMES = [
        'GLOBALS',
        '_SERVER',
        '_GET',
        '_POST',
        '_FILES',
        '_COOKIE',
        '_SESSION',
        '_REQUEST',
        '_ENV',
    ];

    /**
     * @param list<string> $patterns PCRE regexes matched against `Full\Class\Name::methodName`. Empty = global.
     */
    public function __construct(
        private array $patterns = [],
    ) {
    }

    public function getNodeType(): string
    {
        return Variable::class;
    }

    /**
     * @param Variable $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!is_string($node->name)) {
            return [];
        }

        if (!in_array($node->name, self::SUPERGLOBAL_NAMES, true)) {
            return [];
        }

        if (!$this->shouldAnalyzeScope($scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                self::ERROR_MESSAGE,
                $this->formatSuperglobalDisplay($node->name)
            ))
                ->identifier(self::IDENTIFIER)
                ->line($node->getLine())
                ->build(),
        ];
    }

    private function shouldAnalyzeScope(Scope $scope): bool
    {
        if ($this->patterns === []) {
            return true;
        }

        $classReflection = $scope->getClassReflection();
        $functionReflection = $scope->getFunction();
        if ($classReflection === null || $functionReflection === null) {
            return false;
        }

        $fullName = $classReflection->getName() . '::' . $functionReflection->getName();

        return $this->matchesAnyPattern($fullName, $this->patterns);
    }

    private function formatSuperglobalDisplay(string $name): string
    {
        return '$' . $name;
    }
}
