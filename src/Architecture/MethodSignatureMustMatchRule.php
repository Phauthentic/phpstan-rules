<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 * - Checks if the classname plus method name matches a given regex pattern.
 * - Checks the min and max number of parameters.
 * - Checks if the types of the parameters match the expected types.
 * - Checks if the parameter names match the expected patterns.
 * - Checks if the method has the required visibility scope if specified (public, protected, private).
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

    /**
     * @param array<array{
     *     pattern: string,
     *     minParameters: null|int,
     *     maxParameters: null|int,
     *     signature: array<array{
     *         type: string,
     *         pattern: string|null,
     *     }>,
     *     visibilityScope?: string|null
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
     * @param Scope $scope
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $className = $node->name ? $node->name->toString() : '';

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
                    $visibilityScope = $patternConfig['visibilityScope'] ?? 'unknown';
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(self::ERROR_MESSAGE_VISIBILITY_SCOPE, $fullName, $visibilityScope)
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
     * @param array{type: string, pattern: string|null} $expected
     * @param ClassMethod $method
     */
    private function validateParameter(array $expected, ClassMethod $method, int $i, string $fullName): ?\PHPStan\Rules\RuleError
    {
        $validationCase = $this->determineValidationCase($expected, $method, $i);

        return match ($validationCase) {
            'missing_parameter' => RuleErrorBuilder::message(
                sprintf(
                    self::ERROR_MESSAGE_MISSING_PARAMETER,
                    $fullName,
                    $i + 1,
                    $expected['type']
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
                    $expected['type'],
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
                    $method->params[$i]->var->name,
                    $expected['pattern']
                )
            )
            ->identifier(self::IDENTIFIER)
            ->line($method->params[$i]->getLine())
            ->build(),

            default => null,
        };
    }

    /**
     * @param array{type: string, pattern: string|null} $expected
     * @param ClassMethod $method
     */
    private function determineValidationCase(array $expected, ClassMethod $method, int $i): string
    {
        if (!isset($method->params[$i])) {
            return 'missing_parameter';
        }

        $param = $method->params[$i];

        // Check type if specified in configuration
        if (isset($expected['type']) && $expected['type'] !== null) {
            $paramType = $param->type ? $this->getTypeAsString($param->type) : null;
            if ($paramType !== $expected['type']) {
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
     * @param array<string, mixed> $patternConfig
     * @param ClassMethod $method
     */
    private function isValidVisibilityScope(array $patternConfig, ClassMethod $method): bool
    {
        if (!isset($patternConfig['visibilityScope']) || $patternConfig['visibilityScope'] === null) {
            return true;
        }

        return match ($patternConfig['visibilityScope']) {
            'public' => $method->isPublic(),
            'protected' => $method->isProtected(),
            'private' => $method->isPrivate(),
            default => true,
        };
    }

    /**
     * @param array<string, mixed> $patternConfig
     * @param int $paramCount
     * @param string $fullName
     * @param ClassMethod $method
     * @return array<\PHPStan\Rules\RuleError>
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
     * @param array<string, mixed> $patternConfig
     * @param int $paramCount
     * @return bool
     */
    private function isBelowMinParameters(array $patternConfig, int $paramCount): bool
    {
        return $patternConfig['minParameters'] !== null
            && $paramCount < $patternConfig['minParameters'];
    }

    /**
     * @param array<string, mixed> $patternConfig
     * @param int $paramCount
     * @param string $fullName
     * @param ClassMethod $method
     * @return array<\PHPStan\Rules\RuleError>
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
     * @param array<string, mixed> $patternConfig
     * @param int $paramCount
     * @return bool
     */
    private function isAboveMaxParameters(array $patternConfig, int $paramCount): bool
    {
        return $patternConfig['maxParameters'] !== null
            && $paramCount > $patternConfig['maxParameters'];
    }

    /**
     * Checks if the parameter name does not match the expected pattern.
     *
     * @param array{type: string, pattern: string|null} $expected
     * @param \PhpParser\Node\Param $param
     * @return bool
     */
    private function isInvalidParameterName(array $expected, \PhpParser\Node\Param $param): bool
    {
        return isset($expected['pattern'])
            && $expected['pattern'] !== null
            && $param->var->name !== null
            && !preg_match($expected['pattern'], (string)$param->var->name);
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
}
