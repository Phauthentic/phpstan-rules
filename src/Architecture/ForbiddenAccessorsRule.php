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
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 *
 * - Checks if a class matches a given regex pattern.
 * - Forbids public and/or protected getters (getXxx) and/or setters (setXxx) on matched classes.
 * - Accessor types (getters/setters) and visibility are configurable.
 *
 * @implements Rule<Class_>
 */
class ForbiddenAccessorsRule implements Rule
{
    private const ERROR_MESSAGE_GETTER = 'Class %s must not have a %s getter method %s().';
    private const ERROR_MESSAGE_SETTER = 'Class %s must not have a %s setter method %s().';
    private const IDENTIFIER = 'phauthentic.architecture.forbiddenAccessors';

    private const GETTER_PATTERN = '/^get[A-Z]/';
    private const SETTER_PATTERN = '/^set[A-Z]/';

    /**
     * @param array<string> $classPatterns Regex patterns to match against class FQCNs.
     * @param bool $forbidGetters Whether to forbid getXxx() methods.
     * @param bool $forbidSetters Whether to forbid setXxx() methods.
     * @param array<string> $visibility Array of visibilities to check ('public', 'protected').
     */
    public function __construct(
        protected array $classPatterns,
        protected bool $forbidGetters = true,
        protected bool $forbidSetters = true,
        protected array $visibility = ['public']
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @param Scope $scope
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!isset($node->name)) {
            return [];
        }

        $className = $node->name->toString();
        $namespaceName = $scope->getNamespace() ?? '';
        $fullClassName = $namespaceName !== '' ? $namespaceName . '\\' . $className : $className;

        if (!$this->matchesClassPatterns($fullClassName)) {
            return [];
        }

        $errors = [];

        foreach ($node->getMethods() as $method) {
            $methodName = $method->name->toString();
            $methodVisibility = $this->getMethodVisibility($method);

            if (!in_array($methodVisibility, $this->visibility, true)) {
                continue;
            }

            if ($this->forbidGetters && preg_match(self::GETTER_PATTERN, $methodName)) {
                $errors[] = $this->buildGetterError($fullClassName, $methodVisibility, $methodName, $method->getLine());
            }

            if ($this->forbidSetters && preg_match(self::SETTER_PATTERN, $methodName)) {
                $errors[] = $this->buildSetterError($fullClassName, $methodVisibility, $methodName, $method->getLine());
            }
        }

        return $errors;
    }

    /**
     * Check if the class FQCN matches any of the configured patterns.
     */
    private function matchesClassPatterns(string $fullClassName): bool
    {
        foreach ($this->classPatterns as $pattern) {
            if (preg_match($pattern, $fullClassName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the visibility of a method as a string.
     */
    private function getMethodVisibility(Node\Stmt\ClassMethod $method): string
    {
        if ($method->isPublic()) {
            return 'public';
        }

        if ($method->isProtected()) {
            return 'protected';
        }

        return 'private';
    }

    /**
     * @return \PHPStan\Rules\RuleError
     */
    private function buildGetterError(string $fullClassName, string $visibility, string $methodName, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(self::ERROR_MESSAGE_GETTER, $fullClassName, $visibility, $methodName)
        )
            ->identifier(self::IDENTIFIER)
            ->line($line)
            ->build();
    }

    /**
     * @return \PHPStan\Rules\RuleError
     */
    private function buildSetterError(string $fullClassName, string $visibility, string $methodName, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(self::ERROR_MESSAGE_SETTER, $fullClassName, $visibility, $methodName)
        )
            ->identifier(self::IDENTIFIER)
            ->line($line)
            ->build();
    }
}
