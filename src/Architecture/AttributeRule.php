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
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 *
 * - Validates PHP 8+ attributes on classes, methods, and properties.
 * - Supports allowed, forbidden, and required attribute configurations.
 * - Uses regex patterns to match targets (classes, methods, properties) and attributes.
 *
 * Configuration structure:
 * [
 *     'allowed' => [
 *         ['classPattern' => '/regex/', 'attributes' => ['/AttrPattern/']],
 *         ['methodPattern' => '/regex/', 'attributes' => ['/AttrPattern/']],
 *         ['classPattern' => '/regex/', 'propertyPattern' => '/regex/', 'attributes' => ['/AttrPattern/']],
 *     ],
 *     'forbidden' => [
 *         ['classPattern' => '/regex/', 'attributes' => ['/AttrPattern/']],
 *     ],
 *     'required' => [
 *         ['classPattern' => '/regex/', 'attributes' => ['/AttrPattern/']],
 *         ['methodPattern' => '/regex/', 'attributes' => ['/AttrPattern/']],
 *         ['classPattern' => '/regex/', 'propertyPattern' => '/regex/', 'attributes' => ['/AttrPattern/']],
 *     ],
 * ]
 *
 * @implements Rule<Class_>
 */
class AttributeRule implements Rule
{
    private const ERROR_FORBIDDEN = 'Attribute %s is forbidden on %s %s.';

    private const ERROR_NOT_ALLOWED = 'Attribute %s is not in the allowed list for %s %s. Allowed patterns: %s';

    private const ERROR_REQUIRED = 'Missing required attribute matching pattern %s on %s %s.';

    private const IDENTIFIER_FORBIDDEN = 'phauthentic.architecture.attributeForbidden';

    private const IDENTIFIER_NOT_ALLOWED = 'phauthentic.architecture.attributeNotAllowed';

    private const IDENTIFIER_REQUIRED = 'phauthentic.architecture.attributeRequired';

    /**
     * @param array{
     *     allowed?: array<array{
     *         classPattern?: string,
     *         methodPattern?: string,
     *         propertyPattern?: string,
     *         attributes: array<string>
     *     }>,
     *     forbidden?: array<array{
     *         classPattern?: string,
     *         methodPattern?: string,
     *         propertyPattern?: string,
     *         attributes: array<string>
     *     }>,
     *     required?: array<array{
     *         classPattern?: string,
     *         methodPattern?: string,
     *         propertyPattern?: string,
     *         attributes: array<string>
     *     }>
     * } $config
     */
    public function __construct(
        protected array $config
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
        if (!isset($node->name)) {
            return [];
        }

        $className = $node->name->toString();
        $namespaceName = $scope->getNamespace() ?? '';
        $fullClassName = $namespaceName !== '' ? $namespaceName . '\\' . $className : $className;

        /** @var list<IdentifierRuleError> $errors */
        $errors = [];

        // Check class attributes
        $errors = $this->checkClassAttributes($node, $fullClassName, $errors);

        // Check method attributes
        foreach ($node->getMethods() as $method) {
            $errors = $this->checkMethodAttributes($method, $fullClassName, $errors);
        }

        // Check property attributes
        foreach ($node->getProperties() as $property) {
            $errors = $this->checkPropertyAttributes($property, $fullClassName, $errors);
        }

        return $errors;
    }

    /**
     * @param list<IdentifierRuleError> $errors
     * @return list<IdentifierRuleError>
     */
    private function checkClassAttributes(Class_ $node, string $fullClassName, array $errors): array
    {
        $attributes = $this->extractAttributes($node->attrGroups);
        $attributeFqcns = array_map(fn(Attribute $attr) => $this->getAttributeFqcn($attr), $attributes);

        foreach ($attributes as $attribute) {
            $attrFqcn = $this->getAttributeFqcn($attribute);
            $line = $attribute->getLine();
            $isForbidden = false;

            // Check forbidden rules (classPattern only, no method/property pattern)
            foreach ($this->config['forbidden'] ?? [] as $rule) {
                if ($this->isClassOnlyRule($rule) && $this->matchesClassPattern($rule, $fullClassName)) {
                    if ($this->attributeMatchesPatterns($attrFqcn, $rule['attributes'])) {
                        $errors[] = $this->buildForbiddenError($attrFqcn, 'class', $fullClassName, $line);
                        $isForbidden = true;
                    }
                }
            }

            // Check allowed rules (classPattern only, no method/property pattern)
            // Skip if already reported as forbidden
            if (!$isForbidden) {
                foreach ($this->config['allowed'] ?? [] as $rule) {
                    if ($this->isClassOnlyRule($rule) && $this->matchesClassPattern($rule, $fullClassName)) {
                        if (!$this->attributeMatchesPatterns($attrFqcn, $rule['attributes'])) {
                            $errors[] = $this->buildNotAllowedError($attrFqcn, 'class', $fullClassName, $rule['attributes'], $line);
                        }
                    }
                }
            }
        }

        // Check required rules (classPattern only, no method/property pattern)
        foreach ($this->config['required'] ?? [] as $rule) {
            if ($this->isClassOnlyRule($rule) && $this->matchesClassPattern($rule, $fullClassName)) {
                foreach ($rule['attributes'] as $requiredPattern) {
                    if (!$this->hasAttributeMatchingPattern($attributeFqcns, $requiredPattern)) {
                        $errors[] = $this->buildRequiredError($requiredPattern, 'class', $fullClassName, $node->getLine());
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param list<IdentifierRuleError> $errors
     * @return list<IdentifierRuleError>
     */
    private function checkMethodAttributes(ClassMethod $method, string $fullClassName, array $errors): array
    {
        $methodName = $method->name->toString();
        $attributes = $this->extractAttributes($method->attrGroups);
        $attributeFqcns = array_map(fn(Attribute $attr) => $this->getAttributeFqcn($attr), $attributes);

        foreach ($attributes as $attribute) {
            $attrFqcn = $this->getAttributeFqcn($attribute);
            $line = $attribute->getLine();
            $isForbidden = false;

            // Check forbidden rules
            foreach ($this->config['forbidden'] ?? [] as $rule) {
                if ($this->matchesMethodRule($rule, $fullClassName, $methodName)) {
                    if ($this->attributeMatchesPatterns($attrFqcn, $rule['attributes'])) {
                        $errors[] = $this->buildForbiddenError($attrFqcn, 'method', $fullClassName . '::' . $methodName, $line);
                        $isForbidden = true;
                    }
                }
            }

            // Check allowed rules - skip if already reported as forbidden
            if (!$isForbidden) {
                foreach ($this->config['allowed'] ?? [] as $rule) {
                    if ($this->matchesMethodRule($rule, $fullClassName, $methodName)) {
                        if (!$this->attributeMatchesPatterns($attrFqcn, $rule['attributes'])) {
                            $errors[] = $this->buildNotAllowedError($attrFqcn, 'method', $fullClassName . '::' . $methodName, $rule['attributes'], $line);
                        }
                    }
                }
            }
        }

        // Check required rules
        foreach ($this->config['required'] ?? [] as $rule) {
            if ($this->matchesMethodRule($rule, $fullClassName, $methodName)) {
                foreach ($rule['attributes'] as $requiredPattern) {
                    if (!$this->hasAttributeMatchingPattern($attributeFqcns, $requiredPattern)) {
                        $errors[] = $this->buildRequiredError($requiredPattern, 'method', $fullClassName . '::' . $methodName, $method->getLine());
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param list<IdentifierRuleError> $errors
     * @return list<IdentifierRuleError>
     */
    private function checkPropertyAttributes(Property $property, string $fullClassName, array $errors): array
    {
        $propertyNames = [];
        foreach ($property->props as $prop) {
            $propertyNames[] = $prop->name->toString();
        }

        $attributes = $this->extractAttributes($property->attrGroups);
        $attributeFqcns = array_map(fn(Attribute $attr) => $this->getAttributeFqcn($attr), $attributes);

        foreach ($attributes as $attribute) {
            $attrFqcn = $this->getAttributeFqcn($attribute);
            $line = $attribute->getLine();

            foreach ($propertyNames as $propertyName) {
                $isForbidden = false;

                // Check forbidden rules
                foreach ($this->config['forbidden'] ?? [] as $rule) {
                    if ($this->matchesPropertyRule($rule, $fullClassName, $propertyName)) {
                        if ($this->attributeMatchesPatterns($attrFqcn, $rule['attributes'])) {
                            $errors[] = $this->buildForbiddenError($attrFqcn, 'property', $fullClassName . '::$' . $propertyName, $line);
                            $isForbidden = true;
                        }
                    }
                }

                // Check allowed rules - skip if already reported as forbidden
                if (!$isForbidden) {
                    foreach ($this->config['allowed'] ?? [] as $rule) {
                        if ($this->matchesPropertyRule($rule, $fullClassName, $propertyName)) {
                            if (!$this->attributeMatchesPatterns($attrFqcn, $rule['attributes'])) {
                                $errors[] = $this->buildNotAllowedError($attrFqcn, 'property', $fullClassName . '::$' . $propertyName, $rule['attributes'], $line);
                            }
                        }
                    }
                }
            }
        }

        // Check required rules
        foreach ($propertyNames as $propertyName) {
            foreach ($this->config['required'] ?? [] as $rule) {
                if ($this->matchesPropertyRule($rule, $fullClassName, $propertyName)) {
                    foreach ($rule['attributes'] as $requiredPattern) {
                        if (!$this->hasAttributeMatchingPattern($attributeFqcns, $requiredPattern)) {
                            $errors[] = $this->buildRequiredError($requiredPattern, 'property', $fullClassName . '::$' . $propertyName, $property->getLine());
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<\PhpParser\Node\AttributeGroup> $attrGroups
     * @return array<Attribute>
     */
    private function extractAttributes(array $attrGroups): array
    {
        $attributes = [];
        foreach ($attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $attributes[] = $attr;
            }
        }

        return $attributes;
    }

    private function getAttributeFqcn(Attribute $attribute): string
    {
        return $attribute->name->toString();
    }

    /**
     * @param array{classPattern?: string, methodPattern?: string, propertyPattern?: string, attributes: array<string>} $rule
     */
    private function isClassOnlyRule(array $rule): bool
    {
        return isset($rule['classPattern'])
            && !isset($rule['methodPattern'])
            && !isset($rule['propertyPattern']);
    }

    /**
     * @param array{classPattern?: string, methodPattern?: string, propertyPattern?: string, attributes: array<string>} $rule
     */
    private function matchesClassPattern(array $rule, string $fullClassName): bool
    {
        if (!isset($rule['classPattern'])) {
            return false;
        }

        return preg_match($rule['classPattern'], $fullClassName) === 1;
    }

    /**
     * @param array{classPattern?: string, methodPattern?: string, propertyPattern?: string, attributes: array<string>} $rule
     */
    private function matchesMethodRule(array $rule, string $fullClassName, string $methodName): bool
    {
        // Must have methodPattern
        if (!isset($rule['methodPattern'])) {
            return false;
        }

        // If classPattern is set, it must match
        if (isset($rule['classPattern']) && preg_match($rule['classPattern'], $fullClassName) !== 1) {
            return false;
        }

        // Method pattern must match
        return preg_match($rule['methodPattern'], $methodName) === 1;
    }

    /**
     * @param array{classPattern?: string, methodPattern?: string, propertyPattern?: string, attributes: array<string>} $rule
     */
    private function matchesPropertyRule(array $rule, string $fullClassName, string $propertyName): bool
    {
        // Must have propertyPattern
        if (!isset($rule['propertyPattern'])) {
            return false;
        }

        // If classPattern is set, it must match
        if (isset($rule['classPattern']) && preg_match($rule['classPattern'], $fullClassName) !== 1) {
            return false;
        }

        // Property pattern must match
        return preg_match($rule['propertyPattern'], $propertyName) === 1;
    }

    /**
     * @param array<string> $patterns
     */
    private function attributeMatchesPatterns(string $attrFqcn, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $attrFqcn) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if any of the attribute FQCNs match the given pattern.
     *
     * @param array<string> $attributeFqcns
     */
    private function hasAttributeMatchingPattern(array $attributeFqcns, string $pattern): bool
    {
        foreach ($attributeFqcns as $attrFqcn) {
            if (preg_match($pattern, $attrFqcn) === 1) {
                return true;
            }
        }

        return false;
    }

    private function buildForbiddenError(string $attrFqcn, string $targetType, string $targetName, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(self::ERROR_FORBIDDEN, $attrFqcn, $targetType, $targetName))
            ->line($line)
            ->identifier(self::IDENTIFIER_FORBIDDEN)
            ->build();
    }

    /**
     * @param array<string> $allowedPatterns
     */
    private function buildNotAllowedError(string $attrFqcn, string $targetType, string $targetName, array $allowedPatterns, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(
            self::ERROR_NOT_ALLOWED,
            $attrFqcn,
            $targetType,
            $targetName,
            implode(', ', $allowedPatterns)
        ))
            ->line($line)
            ->identifier(self::IDENTIFIER_NOT_ALLOWED)
            ->build();
    }

    private function buildRequiredError(string $requiredPattern, string $targetType, string $targetName, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(self::ERROR_REQUIRED, $requiredPattern, $targetType, $targetName))
            ->line($line)
            ->identifier(self::IDENTIFIER_REQUIRED)
            ->build();
    }
}
