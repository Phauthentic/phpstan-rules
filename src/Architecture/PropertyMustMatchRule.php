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
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 * - Checks if the class name matches a given regex pattern.
 * - For matching classes, validates that properties match expected configurations.
 * - Checks if the property type matches the expected type.
 * - Checks if the property has the required visibility scope (public, protected, private).
 * - When required is set to true, enforces that matching classes must have the property.
 *
 * @phpstan-type PropertyRule array{
 *     name: string,
 *     type?: string|null,
 *     visibilityScope?: string|null,
 *     required?: bool|null,
 *     nullable?: bool|null
 * }
 * @phpstan-type PatternConfig array{
 *     classPattern: string,
 *     properties: array<PropertyRule>
 * }
 * @implements Rule<Class_>
 */
class PropertyMustMatchRule implements Rule
{
    private const IDENTIFIER = 'phauthentic.architecture.propertyMustMatch';

    private const ERROR_MESSAGE_MISSING_PROPERTY = 'Class %s must have property $%s.';
    private const ERROR_MESSAGE_WRONG_TYPE = 'Property %s::$%s should be of type %s, %s given.';
    private const ERROR_MESSAGE_VISIBILITY_SCOPE = 'Property %s::$%s must be %s.';

    /**
     * @param array<PatternConfig> $propertyPatterns
     */
    public function __construct(
        protected array $propertyPatterns
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
        $shortClassName = $node->name?->toString() ?? '';

        if ($shortClassName === '') {
            return [];
        }

        $namespaceName = $scope->getNamespace() ?? '';
        $fullClassName = $namespaceName !== '' ? $namespaceName . '\\' . $shortClassName : $shortClassName;

        $classProperties = $this->getClassProperties($node);
        $matchingPatterns = $this->getMatchingPatterns($fullClassName);

        $errors = [];
        foreach ($matchingPatterns as $patternConfig) {
            $errors = array_merge(
                $errors,
                $this->validatePatternProperties($patternConfig, $classProperties, $fullClassName, $node->getLine())
            );
        }

        return $errors;
    }

    /**
     * @return array<PatternConfig>
     */
    private function getMatchingPatterns(string $className): array
    {
        return array_filter(
            $this->propertyPatterns,
            function (array $config) use ($className): bool {
                $result = @preg_match($config['classPattern'], $className);
                if ($result === false) {
                    throw new \InvalidArgumentException(
                        sprintf('Invalid regex pattern "%s": %s', $config['classPattern'], preg_last_error_msg())
                    );
                }

                return $result === 1;
            }
        );
    }

    /**
     * @param PatternConfig $patternConfig
     * @param array<string, Property> $classProperties
     * @return array<\PHPStan\Rules\RuleError>
     */
    private function validatePatternProperties(
        array $patternConfig,
        array $classProperties,
        string $className,
        int $classLine
    ): array {
        $errors = [];

        foreach ($patternConfig['properties'] as $propertyRule) {
            $errors = array_merge(
                $errors,
                $this->validatePropertyRule($propertyRule, $classProperties, $className, $classLine)
            );
        }

        return $errors;
    }

    /**
     * @param PropertyRule $propertyRule
     * @param array<string, Property> $classProperties
     * @return array<\PHPStan\Rules\RuleError>
     */
    private function validatePropertyRule(
        array $propertyRule,
        array $classProperties,
        string $className,
        int $classLine
    ): array {
        $propertyName = $propertyRule['name'];

        if (!isset($classProperties[$propertyName])) {
            return $this->handleMissingProperty($propertyRule, $className, $propertyName, $classLine);
        }

        return $this->validateExistingProperty($propertyRule, $classProperties[$propertyName], $className, $propertyName);
    }

    /**
     * @param PropertyRule $propertyRule
     * @return array<\PHPStan\Rules\RuleError>
     */
    private function handleMissingProperty(
        array $propertyRule,
        string $className,
        string $propertyName,
        int $classLine
    ): array {
        $isRequired = $propertyRule['required'] ?? false;

        if (!$isRequired) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(self::ERROR_MESSAGE_MISSING_PROPERTY, $className, $propertyName)
            )
            ->identifier(self::IDENTIFIER)
            ->line($classLine)
            ->build()
        ];
    }

    /**
     * @param PropertyRule $propertyRule
     * @return array<\PHPStan\Rules\RuleError>
     */
    private function validateExistingProperty(
        array $propertyRule,
        Property $property,
        string $className,
        string $propertyName
    ): array {
        return array_filter([
            $this->validatePropertyType($propertyRule, $property, $className, $propertyName),
            $this->validateVisibilityScope($propertyRule, $property, $className, $propertyName),
        ]);
    }

    /**
     * Get all properties from a class indexed by name.
     *
     * @param Class_ $node
     * @return array<string, Property>
     */
    private function getClassProperties(Class_ $node): array
    {
        $properties = [];

        foreach ($node->getProperties() as $property) {
            foreach ($property->props as $prop) {
                $properties[$prop->name->toString()] = $property;
            }
        }

        return $properties;
    }

    /**
     * Validate property type against expected type.
     *
     * @param PropertyRule $propertyRule
     * @return \PHPStan\Rules\RuleError|null
     */
    private function validatePropertyType(
        array $propertyRule,
        Property $property,
        string $className,
        string $propertyName
    ): ?\PHPStan\Rules\RuleError {
        if (!isset($propertyRule['type'])) {
            return null;
        }

        $expectedType = $propertyRule['type'];
        $actualType = $this->getTypeAsString($property->type);
        $nullable = $propertyRule['nullable'] ?? false;

        if ($this->typeMatches($actualType, $expectedType, $nullable)) {
            return null;
        }

        return $this->buildTypeError(
            $className,
            $propertyName,
            $this->formatExpectedType($expectedType, $nullable),
            $actualType ?? 'none',
            $property->getLine()
        );
    }

    private function typeMatches(?string $actualType, string $expectedType, bool $nullable): bool
    {
        if ($actualType === $expectedType) {
            return true;
        }

        return $nullable && $actualType === '?' . $expectedType;
    }

    private function formatExpectedType(string $expectedType, bool $nullable): string
    {
        if (!$nullable) {
            return $expectedType;
        }

        return $expectedType . ' or ?' . $expectedType;
    }

    private function buildTypeError(
        string $className,
        string $propertyName,
        string $expectedType,
        string $actualType,
        int $line
    ): \PHPStan\Rules\RuleError {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_WRONG_TYPE,
                $className,
                $propertyName,
                $expectedType,
                $actualType
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    /**
     * Validate property visibility scope.
     *
     * @param PropertyRule $propertyRule
     * @return \PHPStan\Rules\RuleError|null
     */
    private function validateVisibilityScope(
        array $propertyRule,
        Property $property,
        string $className,
        string $propertyName
    ): ?\PHPStan\Rules\RuleError {
        if (!isset($propertyRule['visibilityScope'])) {
            return null;
        }

        $expectedVisibility = $propertyRule['visibilityScope'];
        $isValid = match ($expectedVisibility) {
            'public' => $property->isPublic(),
            'protected' => $property->isProtected(),
            'private' => $property->isPrivate(),
            default => throw new \InvalidArgumentException(
                sprintf('Invalid visibilityScope "%s". Must be one of: public, protected, private.', $expectedVisibility)
            ),
        };

        if (!$isValid) {
            return RuleErrorBuilder::message(
                sprintf(
                    self::ERROR_MESSAGE_VISIBILITY_SCOPE,
                    $className,
                    $propertyName,
                    $expectedVisibility
                )
            )
            ->identifier(self::IDENTIFIER)
            ->line($property->getLine())
            ->build();
        }

        return null;
    }

    /**
     * Convert a type node to string representation.
     */
    private function getTypeAsString(ComplexType|Identifier|Name|null $type): ?string
    {
        return match (true) {
            $type === null => null,
            $type instanceof Identifier => $type->name,
            $type instanceof Name => $type->toString(),
            $type instanceof NullableType =>
                ($inner = $this->getTypeAsString($type->type)) !== null ? '?' . $inner : null,
            $type instanceof UnionType => implode('|', array_filter(
                array_map(fn ($t) => $this->getTypeAsString($t), $type->types)
            )),
            $type instanceof IntersectionType => implode('&', array_filter(
                array_map(fn ($t) => $this->getTypeAsString($t), $type->types)
            )),
            default => null,
        };
    }
}
