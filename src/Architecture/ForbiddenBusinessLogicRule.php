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

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 *
 * - Forbids selected control structures inside class methods: if, for, foreach, while, switch.
 * - Construct names are matched case-insensitively; unknown names in configuration are ignored.
 * - Targeting uses `Full\Class\Name::methodName` from the current scope (class + function reflections).
 *   If either reflection is missing, no errors are reported.
 * - `forbiddenStatements` is the global default list. Every matching scope starts from this list.
 * - `patterns` is a list of `pattern` (regex against `Fqcn::methodName`) and optional `forbiddenStatements`.
 *   For each pattern entry in order: if the regex matches, and `forbiddenStatements` is present, it
 *   replaces the effective list entirely. The last matching entry that defines `forbiddenStatements` wins.
 *   Pattern entries without `forbiddenStatements` do not change the effective list.
 * - If `forbiddenStatements` is empty, nothing is forbidden unless a matching pattern supplies a
 *   non-empty `forbiddenStatements` list.
 * - Legacy configuration: `patterns` may be a list of regex strings; each is normalised to
 *   `{ pattern: string }` without overrides.
 *
 * @implements Rule<Node>
 */
class ForbiddenBusinessLogicRule implements Rule
{
    private const ERROR_MESSAGE = 'Statement "%s" is not allowed in this method.';

    private const IDENTIFIER = 'phauthentic.architecture.forbiddenBusinessLogic';

    /** @var list<string> */
    private const DEFAULT_FORBIDDEN = ['if', 'for', 'foreach', 'while', 'switch'];

    /**
     * @var array<string, string> lowercase construct name => canonical name
     */
    private const KNOWN_CONSTRUCTS = [
        'if' => 'if',
        'for' => 'for',
        'foreach' => 'foreach',
        'while' => 'while',
        'switch' => 'switch',
    ];

    /**
     * @var list<string>
     */
    private array $globalForbiddenStatements;

    /**
     * @var list<array{pattern: string, forbiddenStatements?: list<string>}>
     */
    private array $patterns;

    /**
     * @param list<string> $forbiddenStatements
     * @param array<int, mixed> $patterns Legacy string entries or arrays with `pattern` and optional `forbiddenStatements`.
     */
    public function __construct(
        array $forbiddenStatements = self::DEFAULT_FORBIDDEN,
        array $patterns = [],
    ) {
        $this->globalForbiddenStatements = $this->normalizeConstructNames($forbiddenStatements);
        $this->patterns = $this->normalizePatterns($patterns);
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $construct = $this->nodeToConstructName($node);
        if ($construct === null) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        $functionReflection = $scope->getFunction();
        if ($classReflection === null || $functionReflection === null) {
            return [];
        }

        $fullName = $classReflection->getName() . '::' . $functionReflection->getName();
        $effective = $this->resolveForbiddenStatementsFor($fullName);
        if ($effective === []) {
            return [];
        }

        if (!in_array($construct, $effective, true)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $construct))
                ->identifier(self::IDENTIFIER)
                ->line($node->getLine())
                ->build(),
        ];
    }

    /**
     * @return list<string>
     */
    private function resolveForbiddenStatementsFor(string $fullName): array
    {
        $effective = $this->globalForbiddenStatements;

        foreach ($this->patterns as $entry) {
            if (preg_match($entry['pattern'], $fullName) !== 1) {
                continue;
            }
            if (array_key_exists('forbiddenStatements', $entry)) {
                $effective = $this->normalizeConstructNames($entry['forbiddenStatements']);
            }
        }

        return $effective;
    }

    /**
     * @param list<string> $names
     * @return list<string>
     */
    private function normalizeConstructNames(array $names): array
    {
        $out = [];
        foreach ($names as $name) {
            $key = strtolower($name);
            if (!isset(self::KNOWN_CONSTRUCTS[$key])) {
                continue;
            }
            $canonical = self::KNOWN_CONSTRUCTS[$key];
            $out[$canonical] = $canonical;
        }

        return array_values($out);
    }

    /**
     * @param array<int, mixed> $patterns
     * @return list<array{pattern: string, forbiddenStatements?: list<string>}>
     */
    private function normalizePatterns(array $patterns): array
    {
        $out = [];
        foreach ($patterns as $entry) {
            if (is_string($entry)) {
                $out[] = ['pattern' => $entry];
                continue;
            }
            if (!is_array($entry)) {
                continue;
            }
            $pattern = $entry['pattern'] ?? null;
            if (!is_string($pattern)) {
                continue;
            }
            /** @var array{pattern: string, forbiddenStatements?: list<string>} $normalized */
            $normalized = ['pattern' => $pattern];
            if (array_key_exists('forbiddenStatements', $entry)) {
                $stmts = $entry['forbiddenStatements'];
                if (is_array($stmts)) {
                    /** @var list<string> $list */
                    $list = $stmts;
                    $normalized['forbiddenStatements'] = $list;
                }
            }
            $out[] = $normalized;
        }

        return $out;
    }

    private function nodeToConstructName(Node $node): ?string
    {
        if ($node instanceof If_) {
            return 'if';
        }
        if ($node instanceof For_) {
            return 'for';
        }
        if ($node instanceof Foreach_) {
            return 'foreach';
        }
        if ($node instanceof While_) {
            return 'while';
        }
        if ($node instanceof Switch_) {
            return 'switch';
        }

        return null;
    }
}
