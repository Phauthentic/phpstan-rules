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
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
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
    use ClassNameResolver;

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
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $fullClassName = $this->resolveFullClassName($node, $scope);
        if ($fullClassName === null) {
            return [];
        }

        return [
            ...$this->checkRequiredMethods($node, $fullClassName),
            ...$this->validateMethods($node->getMethods(), $fullClassName),
        ];
    }

    /**
     * @param array<ClassMethod> $methods
     * @return list<IdentifierRuleError>
     */
    private function validateMethods(array $methods, string $className): array
    {
        $errors = [];
        foreach ($methods as $method) {
            $errors = [...$errors, ...$this->validateMethod($method, $className)];
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function validateMethod(ClassMethod $method, string $className): array
    {
        $fullName = $className . '::' . $method->name->toString();
        $errors = [];

        foreach ($this->signaturePatterns as $config) {
            if (preg_match($config['pattern'], $fullName) !== 1) {
                continue;
            }

            $errors = [...$errors, ...$this->validateMethodAgainstConfig($method, $config, $fullName)];
        }

        return $errors;
    }

    /**
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $config
     * @return list<IdentifierRuleError>
     */
    private function validateMethodAgainstConfig(ClassMethod $method, array $config, string $fullName): array
    {
        $errors = [];
        $paramCount = count($method->params);
        $line = $method->getLine();

        $minError = $this->checkMinParameters($config, $paramCount, $fullName, $line);
        if ($minError !== null) {
            $errors[] = $minError;
        }

        $maxError = $this->checkMaxParameters($config, $paramCount, $fullName, $line);
        if ($maxError !== null) {
            $errors[] = $maxError;
        }

        foreach ($config['signature'] as $index => $expected) {
            $paramError = $this->validateParameter($expected, $method, $index, $fullName);
            if ($paramError !== null) {
                $errors[] = $paramError;
            }
        }

        $visibilityError = $this->checkVisibility($config, $method, $fullName);
        if ($visibilityError !== null) {
            $errors[] = $visibilityError;
        }

        return $errors;
    }

    /**
     * @param array{type?: string, pattern?: string|null} $expected
     */
    private function validateParameter(array $expected, ClassMethod $method, int $index, string $fullName): ?IdentifierRuleError
    {
        if (!isset($method->params[$index])) {
            return $this->buildError(
                sprintf(self::ERROR_MESSAGE_MISSING_PARAMETER, $fullName, $index + 1, $expected['type'] ?? 'unknown'),
                $method->getLine()
            );
        }

        $param = $method->params[$index];

        $typeError = $this->validateParameterType($expected, $param, $fullName, $index);
        if ($typeError !== null) {
            return $typeError;
        }

        return $this->validateParameterName($expected, $param, $fullName, $index);
    }

    /**
     * @param array{type?: string, pattern?: string|null} $expected
     */
    private function validateParameterType(array $expected, Param $param, string $fullName, int $index): ?IdentifierRuleError
    {
        $expectedType = $expected['type'] ?? '';
        if ($expectedType === '') {
            return null;
        }

        $actualType = $this->getTypeAsString($param->type);
        if ($actualType === $expectedType) {
            return null;
        }

        return $this->buildError(
            sprintf(self::ERROR_MESSAGE_WRONG_TYPE, $fullName, $index + 1, $expectedType, $actualType ?? 'none'),
            $param->getLine()
        );
    }

    /**
     * @param array{type?: string, pattern?: string|null} $expected
     */
    private function validateParameterName(array $expected, Param $param, string $fullName, int $index): ?IdentifierRuleError
    {
        $pattern = $expected['pattern'] ?? null;
        if ($pattern === null) {
            return null;
        }

        $paramName = $this->getParamName($param);
        if ($paramName === null) {
            return null;
        }

        if (preg_match($pattern, $paramName) === 1) {
            return null;
        }

        return $this->buildError(
            sprintf(self::ERROR_MESSAGE_NAME_PATTERN, $fullName, $index + 1, $paramName, $pattern),
            $param->getLine()
        );
    }

    /**
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $config
     */
    private function checkVisibility(array $config, ClassMethod $method, string $fullName): ?IdentifierRuleError
    {
        $visibilityScope = $config['visibilityScope'] ?? null;
        if ($visibilityScope === null) {
            return null;
        }

        $isValid = match ($visibilityScope) {
            'public' => $method->isPublic(),
            'protected' => $method->isProtected(),
            'private' => $method->isPrivate(),
            default => true,
        };

        if ($isValid) {
            return null;
        }

        return $this->buildError(
            sprintf(self::ERROR_MESSAGE_VISIBILITY_SCOPE, $fullName, $visibilityScope),
            $method->getLine()
        );
    }

    /**
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $config
     */
    private function checkMinParameters(array $config, int $paramCount, string $fullName, int $line): ?IdentifierRuleError
    {
        if ($config['minParameters'] === null || $paramCount >= $config['minParameters']) {
            return null;
        }

        return $this->buildError(
            sprintf(self::ERROR_MESSAGE_MIN_PARAMETERS, $fullName, $paramCount, $config['minParameters']),
            $line
        );
    }

    /**
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $config
     */
    private function checkMaxParameters(array $config, int $paramCount, string $fullName, int $line): ?IdentifierRuleError
    {
        if ($config['maxParameters'] === null || $paramCount <= $config['maxParameters']) {
            return null;
        }

        return $this->buildError(
            sprintf(self::ERROR_MESSAGE_MAX_PARAMETERS, $fullName, $paramCount, $config['maxParameters']),
            $line
        );
    }

    private function getParamName(Param $param): ?string
    {
        if ($param->var instanceof Variable && is_string($param->var->name)) {
            return $param->var->name;
        }
        return null;
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
     * @return list<IdentifierRuleError>
     */
    private function checkRequiredMethods(Class_ $node, string $className): array
    {
        $implementedMethods = array_map(
            static fn(ClassMethod $method): string => $method->name->toString(),
            $node->getMethods()
        );

        $errors = [];
        foreach ($this->signaturePatterns as $config) {
            $error = $this->checkRequiredMethod($config, $className, $implementedMethods, $node->getLine());
            if ($error !== null) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * @param array{pattern: string, minParameters: int|null, maxParameters: int|null, signature: array<array{type: string, pattern: string|null}>, visibilityScope?: string|null, required?: bool|null} $config
     * @param array<string> $implementedMethods
     */
    private function checkRequiredMethod(array $config, string $className, array $implementedMethods, int $line): ?IdentifierRuleError
    {
        if (($config['required'] ?? false) !== true) {
            return null;
        }

        $extracted = $this->extractClassAndMethodFromPattern($config['pattern']);
        if ($extracted === null) {
            return null;
        }

        if (!$this->classMatchesPattern($className, $extracted['classPattern'])) {
            return null;
        }

        if (in_array($extracted['methodName'], $implementedMethods, true)) {
            return null;
        }

        return $this->buildError(
            sprintf(
                self::ERROR_MESSAGE_REQUIRED_METHOD,
                $className,
                $extracted['methodName'],
                $this->formatSignatureForError($config)
            ),
            $line
        );
    }

    private function buildError(string $message, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier(self::IDENTIFIER)
            ->line($line)
            ->build();
    }
}
