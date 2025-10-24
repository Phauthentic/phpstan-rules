<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 *
 * - Checks if the classname plus method name matches a given regex pattern.
 * - Checks if the method returns the expected type or object, is nullable or void.
 * - Check if the types of the parameters match the expected types.
 * - If an object type is expected, it can match a specific class or a pattern.
 * - Supports union types with "oneOf" (one type must match) and "allOf" (all types must match).
 */
class MethodMustReturnTypeRule implements Rule
{
    private const IDENTIFIER = 'phauthentic.architecture.methodMustReturnType';

    private const ERROR_MESSAGE_VOID = 'Method %s must have a void return type.';
    private const ERROR_MESSAGE_MISSING_RETURN_TYPE = 'Method %s must have a return type of %s.';
    private const ERROR_MESSAGE_NULLABILITY = 'Method %s return type nullability does not match: expected %s.';
    private const ERROR_MESSAGE_OBJECT_TYPE_PATTERN = 'Method %s must return an object matching pattern %s, %s given.';
    private const ERROR_MESSAGE_OBJECT_TYPE = 'Method %s must return an object type.';
    private const ERROR_MESSAGE_TYPE_MISMATCH = 'Method %s must have return type %s, %s given.';
    private const ERROR_MESSAGE_ONE_OF_MISMATCH = 'Method %s must have one of the return types: %s, %s given.';
    private const ERROR_MESSAGE_ALL_OF_MISMATCH = 'Method %s must have all of the return types: %s, %s given.';

    /**
     * @param array<array{
     *     pattern: string,
     *     type?: string,
     *     nullable?: bool,
     *     void?: bool,
     *     objectTypePattern?: string|null,
     *     oneOf?: array<string>,
     *     allOf?: array<string>,
     *     anyOf?: array<string>,
     * }> $returnTypePatterns
     */
    public function __construct(
        protected array $returnTypePatterns
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @param Scope $scope
     * @return array
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $className = $node->name ? $node->name->toString() : '';

        foreach ($node->getMethods() as $method) {
            $methodName = $method->name->toString();
            $fullName = $className . '::' . $methodName;

            foreach ($this->returnTypePatterns as $patternConfig) {
                if (!preg_match($patternConfig['pattern'], $fullName)) {
                    continue;
                }

                // Normalize configuration with defaults
                $config = $this->normalizeConfig($patternConfig);

                $returnTypeNode = $method->getReturnType();
                $returnType = $this->getTypeAsString($returnTypeNode);

                // Check for void
                if ($this->shouldErrorOnVoid($config, $returnType)) {
                    $errors[] = $this->buildVoidError($fullName, $method->getLine());
                    continue;
                }

                // Check for missing return type
                if ($this->shouldErrorOnMissingReturnType($returnType)) {
                    $expectedType = $this->getExpectedTypeDescription($config);
                    $errors[] = $this->buildMissingReturnTypeError($fullName, $expectedType, $method->getLine());
                    continue;
                }

                // Check for nullable
                $isNullable = $this->isNullableType($returnTypeNode);

                if ($this->shouldErrorOnNullability($config, $isNullable)) {
                    $errors[] = $this->buildNullabilityError($fullName, $config['nullable'], $method->getLine());
                }

                // Check for union types (oneOf/allOf/anyOf)
                if (isset($config['oneOf'])) {
                    if ($this->shouldErrorOnOneOf($config['oneOf'], $returnType)) {
                        $errors[] = $this->buildOneOfError($fullName, $config['oneOf'], $returnType, $method->getLine());
                        continue;
                    }
                } elseif (isset($config['allOf'])) {
                    if ($this->shouldErrorOnAllOf($config['allOf'], $returnType)) {
                        $errors[] = $this->buildAllOfError($fullName, $config['allOf'], $returnType, $method->getLine());
                        continue;
                    }
                } else {
                    // Check for single type
                    $expectedType = $config['type'] ?? 'void';
                    if ($expectedType === 'object') {
                        // Unwrap NullableType to get the inner type
                        $innerType = $returnTypeNode instanceof NullableType
                            ? $returnTypeNode->type
                            : $returnTypeNode;

                        if ($innerType instanceof Name) {
                            $objectType = $innerType->toString();
                            if ($this->shouldErrorOnObjectTypePattern($config, $objectType)) {
                                $errors[] = $this->buildObjectTypePatternError($fullName, $config['objectTypePattern'], $objectType, $method->getLine());
                            }
                        } else {
                            $errors[] = $this->buildObjectTypeError($fullName, $method->getLine());
                        }
                    } elseif ($this->shouldErrorOnTypeMismatch($returnType, $expectedType)) {
                        $errors[] = $this->buildTypeMismatchError($fullName, $expectedType, $returnType, $method->getLine());
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Normalize configuration with defaults
     *
     * @param array $config
     * @return array
     */
    private function normalizeConfig(array $config): array
    {
        $normalized = $config;

        // Set defaults
        $normalized['nullable'] = $config['nullable'] ?? false;
        $normalized['void'] = $config['void'] ?? false;
        $normalized['objectTypePattern'] = $config['objectTypePattern'] ?? null;

        // Support 'anyOf' as alias for 'oneOf'
        if (isset($config['anyOf']) && !isset($config['oneOf'])) {
            $normalized['oneOf'] = $config['anyOf'];
        }

        return $normalized;
    }

    private function shouldErrorOnVoid(array $patternConfig, ?string $returnType): bool
    {
        return $patternConfig['void'] && $returnType !== 'void';
    }

    private function buildVoidError(string $fullName, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_VOID,
                $fullName
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    private function shouldErrorOnMissingReturnType(?string $returnType): bool
    {
        return $returnType === null;
    }

    private function getExpectedTypeDescription(array $patternConfig): string
    {
        if (isset($patternConfig['oneOf'])) {
            return 'one of: ' . implode(', ', $patternConfig['oneOf']);
        }
        if (isset($patternConfig['allOf'])) {
            return 'all of: ' . implode(', ', $patternConfig['allOf']);
        }
        return $patternConfig['type'] ?? 'void';
    }

    private function buildMissingReturnTypeError(string $fullName, string $expectedType, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_MISSING_RETURN_TYPE,
                $fullName,
                $expectedType
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    private function shouldErrorOnNullability(array $patternConfig, bool $isNullable): bool
    {
        return $patternConfig['nullable'] !== $isNullable;
    }

    private function buildNullabilityError(string $fullName, bool $expectedNullable, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_NULLABILITY,
                $fullName,
                $expectedNullable ? 'nullable' : 'non-nullable'
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    private function shouldErrorOnOneOf(array $expectedTypes, ?string $returnType): bool
    {
        if ($returnType === null) {
            return true;
        }

        foreach ($expectedTypes as $expectedType) {
            if ($this->isTypeMatchWithRegex($returnType, $expectedType)) {
                return false;
            }
        }

        return true;
    }

    private function buildOneOfError(string $fullName, array $expectedTypes, ?string $actualType, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_ONE_OF_MISMATCH,
                $fullName,
                implode(', ', $expectedTypes),
                $actualType ?? 'no return type'
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    private function shouldErrorOnAllOf(array $expectedTypes, ?string $returnType): bool
    {
        if ($returnType === null) {
            return true;
        }

        // For allOf, we need to check if the return type is a union type that contains all expected types
        // This is a simplified implementation - in practice, you might need more sophisticated union type parsing
        $actualTypes = $this->parseUnionType($returnType);

        foreach ($expectedTypes as $expectedType) {
            $found = false;
            foreach ($actualTypes as $actualType) {
                if ($this->isTypeMatchWithRegex($actualType, $expectedType)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return true;
            }
        }

        return false;
    }

    private function buildAllOfError(string $fullName, array $expectedTypes, ?string $actualType, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_ALL_OF_MISMATCH,
                $fullName,
                implode(', ', $expectedTypes),
                $actualType ?? 'no return type'
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    private function parseUnionType(?string $type): array
    {
        if ($type === null) {
            return [];
        }

        // Simple union type parsing - split by '|' and trim
        return array_map('trim', explode('|', $type));
    }

    private function isTypeMatchWithRegex(?string $actual, string $expected): bool
    {
        if ($actual === null) {
            return false;
        }

        // Check if expected type is a regex pattern
        if (str_starts_with($expected, 'regex:')) {
            $pattern = substr($expected, 6); // Remove 'regex:' prefix
            return (bool) preg_match($pattern, $actual);
        }

        // Use the original isTypeMatch for non-regex types
        return $this->isTypeMatch($actual, $expected);
    }

    private function isTypeMatch(?string $actual, string $expected): bool
    {
        if ($actual === null) {
            return false;
        }

        // Direct match
        if ($actual === $expected) {
            return true;
        }

        // Handle nullable types
        if ($this->isNullableMatch($actual, $expected)) {
            return true;
        }

        // Handle union types
        $actualTypes = $this->parseUnionType($actual);
        foreach ($actualTypes as $actualType) {
            if ($actualType === $expected || $this->isNullableMatch($actualType, $expected)) {
                return true;
            }
        }

        return false;
    }

    private function shouldErrorOnObjectTypePattern(array $patternConfig, string $objectType): bool
    {
        return $patternConfig['objectTypePattern'] !== null &&
            !preg_match($patternConfig['objectTypePattern'], $objectType);
    }

    private function buildObjectTypePatternError(string $fullName, string $pattern, string $objectType, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_OBJECT_TYPE_PATTERN,
                $fullName,
                $pattern,
                $objectType
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    private function buildObjectTypeError(string $fullName, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_OBJECT_TYPE,
                $fullName
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    private function shouldErrorOnTypeMismatch(?string $returnType, string $expectedType): bool
    {
        return !$this->isTypeMatch($returnType, $expectedType);
    }

    private function buildTypeMismatchError(string $fullName, string $expectedType, ?string $actualType, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_TYPE_MISMATCH,
                $fullName,
                $expectedType,
                $actualType ?? 'no return type'
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    private function getTypeAsString(mixed $type): ?string
    {
        $nullableInner = null;
        if ($type instanceof NullableType) {
            $nullableInner = $this->getTypeAsString($type->type);
        }

        return match (true) {
            $type === null => null,
            $type instanceof Identifier => $type->name,
            $type instanceof Name => $type->toString(),
            $type instanceof NullableType => $nullableInner !== null ? '?' . $nullableInner : null,
            default => null,
        };
    }

    private function isNullableType($type): bool
    {
        return $type instanceof NullableType;
    }

    private function isNullableMatch(string $actual, string $expected): bool
    {
        // Handles cases like '?int' vs 'int'
        if (ltrim($actual, '?') === $expected) {
            return true;
        }
        return false;
    }
}
