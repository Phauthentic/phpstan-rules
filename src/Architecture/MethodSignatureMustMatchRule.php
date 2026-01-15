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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 * - Checks if the classname plus method name matches a given regex pattern.
 * - Checks the min and max number of parameters.
 * - Checks if the types of the parameters match the expected types.
 * - Checks if the parameter names match the expected patterns.
 * - Checks if the method has the required visibility scope if specified (public, protected, private).
 * - When required is set to true, enforces that matching classes must implement the method with the specified signature.
 *
 * @implements Rule<Class_>
 */
class MethodSignatureMustMatchRule implements Rule
{
    private const IDENTIFIER = 'phauthentic.architecture.methodSignatureMustMatch';

    private const ERROR_MESSAGE_MISSING_PARAMETER = 'Method %s is missing parameter #%d of type %s.';
    private const ERROR_MESSAGE_WRONG_TYPE = 'Method %s parameter #%d should be of type %s, %s given.';
    private const ERROR_MESSAGE_NAME_PATTERN = 'Method %s parameter #%d name "%s" does not match pattern %s.';
    private const ERROR_MESSAGE_MIN_PARAMETERS = 'Method %s has %d parameters, but at least %d required.';
    private const ERROR_MESSAGE_MAX_PARAMETERS = 'Method %s has %d parameters, but at most %d allowed.';
    private const ERROR_MESSAGE_VISIBILITY_SCOPE = 'Method %s must be %s.';
    private const ERROR_MESSAGE_REQUIRED_METHOD = 'Class %s must implement method %s with signature: %s.';

    /**
     * @param array<array{
     *     pattern: string,
     *     minParameters: null|int,
     *     maxParameters: null|int,
     *     signature: array<array{
     *         type: string,
     *         pattern: string|null,
     *     }>,
     *     visibilityScope?: string|null,
     *     required?: bool|null
     * }> $signaturePatterns
     */
    public function __construct(
        protected array $signaturePatterns
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return list<RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $className = $node->name ? $node->name->toString() : '';

        // Check for required methods first
        $requiredMethodErrors = $this->checkRequiredMethods($node, $className);
        foreach ($requiredMethodErrors as $error) {
            $errors[] = $error;
        }

        foreach ($node->getMethods() as $method) {
            $methodName = $method->name->toString();
            $fullName = $className . '::' . $methodName;

            foreach ($this->signaturePatterns as $patternConfig) {
                if (!preg_match($patternConfig['pattern'], $fullName)) {
                    continue;
                }

                $paramCount = count($method->params);

                $minParamErrors = $this->checkMinParameters(
                    patternConfig: $patternConfig,
                    paramCount: $paramCount,
                    fullName: $fullName,
                    method: $method
                );

                $maxParamErrors = $this->checkMaxParameters(
                    patternConfig: $patternConfig,
                    paramCount: $paramCount,
                    fullName: $fullName,
                    method: $method
                );

                foreach ([$minParamErrors, $maxParamErrors] as $paramErrors) {
                    foreach ($paramErrors as $error) {
                        $errors[] = $error;
                    }
                }

                // Check parameter types and patterns
                if (!empty($patternConfig['signature'])) {
                    foreach ($patternConfig['signature'] as $i => $expected) {
                        $validationResult = $this->validateParameter($expected, $method, $i, $fullName);
                        if ($validationResult !== null) {
                            $errors[] = $validationResult;
                        }
                    }
                }

                if (!$this->isValidVisibilityScope($patternConfig, $method)) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(self::ERROR_MESSAGE_VISIBILITY_SCOPE, $fullName, $patternConfig['visibilityScope'] ?? '')
                    )
                    ->identifier(self::IDENTIFIER)
                    ->line($method->getLine())
                    ->build();
                }
            }
        }

        return $errors;
    }

    /**
     * @param array{type?: string, pattern?: string|null} $expected
     */
    private function validateParameter(array $expected, ClassMethod $method, int $i, string $fullName): ?RuleError
    {
        $validationCase = $this->determineValidationCase($expected, $method, $i);

        return match ($validationCase) {
            'missing_parameter' => RuleErrorBuilder::message(
                sprintf(
                    self::ERROR_MESSAGE_MISSING_PARAMETER,
                    $fullName,
                    $i + 1,
                    $expected['type'] ?? 'unknown'
                )
            )
            ->identifier(self::IDENTIFIER)
            ->line($method->getLine())
            ->build(),

            'wrong_type' => RuleErrorBuilder::message(
                sprintf(
                    self::ERROR_MESSAGE_WRONG_TYPE,
                    $fullName,
                    $i + 1,
                    $expected['type'] ?? 'unknown',
                    $this->getTypeAsString($method->params[$i]->type) ?? 'none'
                )
            )
            ->identifier(self::IDENTIFIER)
            ->line($method->params[$i]->getLine())
            ->build(),

            'invalid_name' => RuleErrorBuilder::message(
                sprintf(
                    self::ERROR_MESSAGE_NAME_PATTERN,
                    $fullName,
                    $i + 1,
                    $this->getParamName($method->params[$i]),
                    $expected['pattern'] ?? ''
                )
            )
            ->identifier(self::IDENTIFIER)
            ->line($method->params[$i]->getLine())
            ->build(),

            default => null,
        };
    }

    /**
     * @param array{type?: string, pattern?: string|null} $expected
     */
    private function determineValidationCase(array $expected, ClassMethod $method, int $i): string
    {
        if (!isset($method->params[$i])) {
            return 'missing_parameter';
        }

        $param = $method->params[$i];

        // Check type if specified in configuration (only if type key exists and is non-empty)
        $expectedType = $expected['type'] ?? '';
        if ($expectedType !== '') {
            $paramType = $param->type ? $this->getTypeAsString($param->type) : null;
            if ($paramType !== $expectedType) {
                return 'wrong_type';
            }
        }

        // Check name pattern
        if ($this->isInvalidParameterName(expected: $expected, param: $param)) {
            return 'invalid_name';
        }

        return 'valid';
    }

    /**
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $patternConfig
     */
    private function isValidVisibilityScope(array $patternConfig, ClassMethod $method): bool
    {
        $visibilityScope = $patternConfig['visibilityScope'] ?? null;
        if ($visibilityScope === null) {
            return true;
        }

        return match ($visibilityScope) {
            'public' => $method->isPublic(),
            'protected' => $method->isProtected(),
            'private' => $method->isPrivate(),
            default => true,
        };
    }

    /**
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $patternConfig
     * @return list<IdentifierRuleError>
     */
    private function checkMinParameters(
        array $patternConfig,
        int $paramCount,
        string $fullName,
        ClassMethod $method
    ): array {
        if ($this->isBelowMinParameters($patternConfig, $paramCount)) {
            return [
                RuleErrorBuilder::message(
                    message: sprintf(
                        self::ERROR_MESSAGE_MIN_PARAMETERS,
                        $fullName,
                        $paramCount,
                        $patternConfig['minParameters']
                    )
                )
                ->identifier(self::IDENTIFIER)
                ->line($method->getLine())
                ->build()
            ];
        }

        return [];
    }

    /**
     * Checks if the parameter count is below the minimum required.
     *
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $patternConfig
     */
    private function isBelowMinParameters(array $patternConfig, int $paramCount): bool
    {
        return $patternConfig['minParameters'] !== null
            && $paramCount < $patternConfig['minParameters'];
    }

    /**
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $patternConfig
     * @return list<IdentifierRuleError>
     */
    private function checkMaxParameters(
        array $patternConfig,
        int $paramCount,
        string $fullName,
        ClassMethod $method
    ): array {
        if ($this->isAboveMaxParameters($patternConfig, $paramCount)) {
            return [
                RuleErrorBuilder::message(
                    message: sprintf(
                        self::ERROR_MESSAGE_MAX_PARAMETERS,
                        $fullName,
                        $paramCount,
                        $patternConfig['maxParameters']
                    )
                )
                ->identifier(self::IDENTIFIER)
                ->line($method->getLine())
                ->build()
            ];
        }
        return [];
    }

    /**
     * Checks if the parameter count is above the maximum allowed.
     *
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $patternConfig
     */
    private function isAboveMaxParameters(array $patternConfig, int $paramCount): bool
    {
        return $patternConfig['maxParameters'] !== null
            && $paramCount > $patternConfig['maxParameters'];
    }

    /**
     * Checks if the parameter name does not match the expected pattern.
     *
     * @param array{type?: string, pattern?: string|null} $expected
     */
    private function isInvalidParameterName(array $expected, Param $param): bool
    {
        $pattern = $expected['pattern'] ?? null;
        if ($pattern === null) {
            return false;
        }
        $paramName = $this->getParamName($param);
        if ($paramName === null) {
            return false;
        }
        return !preg_match($pattern, $paramName);
    }

    /**
     * Get the name of a parameter safely.
     */
    private function getParamName(Param $param): ?string
    {
        if ($param->var instanceof Variable && is_string($param->var->name)) {
            return $param->var->name;
        }
        return null;
    }

    private function getTypeAsString(mixed $type): ?string
    {
        return match (true) {
            $type === null => null,
            $type instanceof Identifier => $type->name,
            $type instanceof Name => $type->toString(),
            $type instanceof NullableType =>
                ($inner = $this->getTypeAsString($type->type)) !== null ? '?' . $inner : null,
            default => null,
        };
    }

    /**
     * Extract class name pattern and method name from a regex pattern.
     * Expected pattern format: '/^ClassName::methodName$/' or '/ClassName::methodName$/'
     *
     * @return array{classPattern: string, methodName: string}|null Array with 'classPattern' and 'methodName', or null if parsing fails
     */
    private function extractClassAndMethodFromPattern(string $pattern): ?array
    {
        // Remove pattern delimiters and anchors
        $cleaned = preg_replace('/^\/\^?/', '', $pattern);
        if ($cleaned === null) {
            return null;
        }
        $cleaned = preg_replace('/\$?\/$/', '', $cleaned);

        if ($cleaned === null || !str_contains($cleaned, '::')) {
            return null;
        }

        $parts = explode('::', $cleaned, 2);
        if (count($parts) !== 2) {
            return null;
        }

        return [
            'classPattern' => $parts[0],
            'methodName' => $parts[1],
        ];
    }

    /**
     * Check if a class name matches a pattern extracted from regex.
     *
     * @param string $className
     * @param string $classPattern
     * @return bool
     */
    private function classMatchesPattern(string $className, string $classPattern): bool
    {
        // Build a regex from the class pattern
        $regex = '/^' . $classPattern . '$/';
        return preg_match($regex, $className) === 1;
    }

    /**
     * Format the expected method signature for error messages.
     *
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $patternConfig
     */
    private function formatSignatureForError(array $patternConfig): string
    {
        $parts = [];

        // Add visibility scope if specified
        $visibilityScope = $patternConfig['visibilityScope'] ?? null;
        if ($visibilityScope !== null) {
            $parts[] = $visibilityScope;
        }

        $parts[] = 'function';

        // Extract method name from pattern
        $extracted = $this->extractClassAndMethodFromPattern($patternConfig['pattern']);
        if ($extracted !== null) {
            $parts[] = $extracted['methodName'];
        }

        // Build parameters
        $params = [];
        if (!empty($patternConfig['signature'])) {
            foreach ($patternConfig['signature'] as $i => $sig) {
                $paramParts = [];
                if ($sig['type'] !== '') {
                    $paramParts[] = $sig['type'];
                }
                $paramParts[] = '$param' . ($i + 1);
                $params[] = implode(' ', $paramParts);
            }
        }

        return implode(' ', $parts) . '(' . implode(', ', $params) . ')';
    }

    /**
     * Check if required methods are implemented in the class.
     *
     * @return list<IdentifierRuleError>
     */
    private function checkRequiredMethods(Class_ $node, string $className): array
    {
        $errors = [];

        // Get list of implemented methods
        $implementedMethods = [];
        foreach ($node->getMethods() as $method) {
            $implementedMethods[] = $method->name->toString();
        }

        // Check each pattern with required flag
        foreach ($this->signaturePatterns as $patternConfig) {
            // Skip if not required
            if (!isset($patternConfig['required']) || $patternConfig['required'] !== true) {
                continue;
            }

            // Extract class and method patterns
            $extracted = $this->extractClassAndMethodFromPattern($patternConfig['pattern']);
            if ($extracted === null) {
                continue;
            }

            // Check if class matches the pattern
            if (!$this->classMatchesPattern($className, $extracted['classPattern'])) {
                continue;
            }

            // Check if method is implemented
            if (!in_array($extracted['methodName'], $implementedMethods, true)) {
                $signature = $this->formatSignatureForError($patternConfig);
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        self::ERROR_MESSAGE_REQUIRED_METHOD,
                        $className,
                        $extracted['methodName'],
                        $signature
                    )
                )
                ->identifier(self::IDENTIFIER)
                ->line($node->getLine())
                ->build();
            }
        }

        return $errors;
    }
}
