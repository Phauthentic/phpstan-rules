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
 * - Checks if the classname plus method name matches a given regex pattern.
 * - Checks if the method returns the expected type or object, is nullable or void.
 * - Check if the types of the parameters match the expected types.
 * - If an object type is expected, it can match a specific class or a pattern.
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

    /**
     * @param array<array{
     *     pattern: string,
     *     type: string,
     *     nullable: bool,
     *     void: bool,
     *     objectTypePattern: string|null,
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

                $returnTypeNode = $method->getReturnType();
                $returnType = $this->getTypeAsString($returnTypeNode);

                // Check for void
                if ($this->shouldErrorOnVoid($patternConfig, $returnType)) {
                    $errors[] = $this->buildVoidError($fullName, $method->getLine());
                    continue;
                }

                // Check for missing return type
                if ($this->shouldErrorOnMissingReturnType($returnType)) {
                    $errors[] = $this->buildMissingReturnTypeError($fullName, $patternConfig['type'], $method->getLine());
                    continue;
                }

                // Check for nullable
                $isNullable = $this->isNullableType($returnTypeNode);

                if ($this->shouldErrorOnNullability($patternConfig, $isNullable)) {
                    $errors[] = $this->buildNullabilityError($fullName, $patternConfig['nullable'], $method->getLine());
                }

                // Check for type
                if ($patternConfig['type'] === 'object') {
                    if ($returnTypeNode instanceof Name) {
                        $objectType = $returnTypeNode->toString();
                        if ($this->shouldErrorOnObjectTypePattern($patternConfig, $objectType)) {
                            $errors[] = $this->buildObjectTypePatternError($fullName, $patternConfig['objectTypePattern'], $objectType, $method->getLine());
                        }
                    } else {
                        $errors[] = $this->buildObjectTypeError($fullName, $method->getLine());
                    }
                } elseif ($this->shouldErrorOnTypeMismatch($returnType, $patternConfig['type'])) {
                    $errors[] = $this->buildTypeMismatchError($fullName, $patternConfig['type'], $returnType, $method->getLine());
                }
            }
        }

        return $errors;
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
        return $returnType !== $expectedType && !$this->isNullableMatch($returnType, $expectedType);
    }

    private function buildTypeMismatchError(string $fullName, string $expectedType, ?string $actualType, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_TYPE_MISMATCH,
                $fullName,
                $expectedType,
                $actualType
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
