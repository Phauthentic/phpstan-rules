<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * - Checks if the classname plus method name matches a given regex pattern.
 * - Checks if the method returns the expected type or object, is nullable or void.
 * - Check if the types of the parameters match the expected types.
 * - If an object type is expected, it can match a specific class or a pattern.
 */
class MethodMustReturnTypeRule implements Rule
{
    /**
     * @var array<array{
     *     pattern: string,
     *     type: string,
     *     nullable: bool,
     *     void: bool,
     *     objectTypePattern: string|null,
     * }>
     */
    private array $returnTypePatterns;

    /**
     * @param array<array{
     *     pattern: string,
     *     type: string,
     *     nullable: bool,
     *     void: bool,
     *     objectTypePattern: string|null,
     * }> $returnTypePatterns
     */
    public function __construct(array $returnTypePatterns)
    {
        $this->returnTypePatterns = $returnTypePatterns;
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
                if ($patternConfig['void']) {
                    if ($returnType !== 'void') {
                        $errors[] = RuleErrorBuilder::message(
                            sprintf(
                                'Method %s must have a void return type.',
                                $fullName
                            )
                        )->line($method->getLine())->build();
                    }
                    continue;
                }

                // Check for missing return type
                if ($returnType === null) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(
                            'Method %s must have a return type of %s.',
                            $fullName,
                            $patternConfig['type']
                        )
                    )->line($method->getLine())->build();
                    continue;
                }

                // Check for nullable
                $isNullable = $this->isNullableType($returnTypeNode);

                if ($patternConfig['nullable'] !== $isNullable) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(
                            'Method %s return type nullability does not match: expected %s.',
                            $fullName,
                            $patternConfig['nullable'] ? 'nullable' : 'non-nullable'
                        )
                    )->line($method->getLine())->build();
                }

                // Check for type
                if ($patternConfig['type'] === 'object') {
                    if ($returnTypeNode instanceof \PhpParser\Node\Name) {
                        $objectType = $returnTypeNode->toString();
                        if ($patternConfig['objectTypePattern'] !== null &&
                            !preg_match($patternConfig['objectTypePattern'], $objectType)
                        ) {
                            $errors[] = RuleErrorBuilder::message(
                                sprintf(
                                    'Method %s must return an object matching pattern %s, %s given.',
                                    $fullName,
                                    $patternConfig['objectTypePattern'],
                                    $objectType
                                )
                            )->line($method->getLine())->build();
                        }
                    } else {
                        $errors[] = RuleErrorBuilder::message(
                            sprintf(
                                'Method %s must return an object type.',
                                $fullName
                            )
                        )->line($method->getLine())->build();
                    }
                } elseif ($returnType !== $patternConfig['type'] && !$this->isNullableMatch($returnType, $patternConfig['type'])) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(
                            'Method %s must have return type %s, %s given.',
                            $fullName,
                            $patternConfig['type'],
                            $returnType
                        )
                    )->line($method->getLine())->build();
                }
            }
        }

        return $errors;
    }

    private function getTypeAsString($type): ?string
    {
        if ($type === null) {
            return null;
        }
        if ($type instanceof \PhpParser\Node\Identifier) {
            return $type->name;
        }
        if ($type instanceof \PhpParser\Node\Name) {
            return $type->toString();
        }
        if ($type instanceof \PhpParser\Node\NullableType) {
            $inner = $this->getTypeAsString($type->type);
            return $inner !== null ? '?' . $inner : null;
        }
        return null;
    }

    private function isNullableType($type): bool
    {
        return $type instanceof \PhpParser\Node\NullableType;
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
