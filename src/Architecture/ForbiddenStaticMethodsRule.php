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
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 *
 * - Checks static method calls in PHP code.
 * - A static method call matching a given regex pattern (FQCN::methodName) is not allowed.
 * - Supports namespace-level, class-level, and method-level granularity.
 * - Reports an error if a forbidden static method call is detected.
 *
 * @implements Rule<StaticCall>
 */
class ForbiddenStaticMethodsRule implements Rule
{
    private const ERROR_MESSAGE = 'Static method call "%s" is forbidden.';

    private const IDENTIFIER = 'phauthentic.architecture.forbiddenStaticMethods';

    /**
     * An array of regex patterns for forbidden static method calls.
     * Patterns match against FQCN::methodName format.
     *
     * @var array<string>
     */
    private array $forbiddenStaticMethods;

    /**
     * @param array<string> $forbiddenStaticMethods
     */
    public function __construct(array $forbiddenStaticMethods)
    {
        $this->forbiddenStaticMethods = $forbiddenStaticMethods;
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Skip dynamic method names (e.g., DateTime::$method())
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $className = $this->resolveClassName($node, $scope);
        if ($className === null) {
            return [];
        }

        $methodName = $node->name->toString();
        $fullName = $className . '::' . $methodName;

        foreach ($this->forbiddenStaticMethods as $forbiddenPattern) {
            if (preg_match($forbiddenPattern, $fullName)) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        self::ERROR_MESSAGE,
                        $fullName
                    ))
                    ->identifier(self::IDENTIFIER)
                    ->line($node->getLine())
                    ->build()
                ];
            }
        }

        return [];
    }

    /**
     * Resolves the class name from a static call node.
     * Handles Name nodes and self/static/parent keywords.
     */
    private function resolveClassName(StaticCall $node, Scope $scope): ?string
    {
        $class = $node->class;

        // Skip dynamic class names (e.g., $class::method())
        if (!$class instanceof Name) {
            return null;
        }

        $className = $class->toString();

        // Handle self, static, parent keywords
        if (in_array($className, ['self', 'static', 'parent'], true)) {
            $classReflection = $scope->getClassReflection();
            if ($classReflection === null) {
                return null;
            }

            if ($className === 'parent') {
                $parentClass = $classReflection->getParentClass();
                if ($parentClass === null) {
                    return null;
                }
                return $parentClass->getName();
            }

            return $classReflection->getName();
        }

        // For fully qualified names, return as-is
        if ($class instanceof Name\FullyQualified) {
            return $className;
        }

        // For non-fully-qualified names, we need to resolve them
        // PHPStan's scope can help us resolve the actual class name
        return $className;
    }
}
