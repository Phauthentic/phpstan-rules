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

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;

/**
 * Trait providing common FQCN resolution and type conversion utilities for PHPStan rules.
 */
trait ClassNameResolver
{
    /**
     * Build FQCN from a Class_ or Interface_ node and scope.
     *
     * @param Class_|Interface_ $node
     * @param Scope $scope
     * @return string|null Returns null if the node has no name (anonymous class)
     */
    protected function resolveFullClassName(Class_|Interface_ $node, Scope $scope): ?string
    {
        if (!isset($node->name)) {
            return null;
        }

        $className = $node->name->toString();
        $namespaceName = $scope->getNamespace() ?? '';

        return $namespaceName !== '' ? $namespaceName . '\\' . $className : $className;
    }

    /**
     * Convert a type node to its string representation.
     *
     * Handles Identifier, Name, NullableType, UnionType, and IntersectionType.
     *
     * @param ComplexType|Identifier|Name|null $type
     * @return string|null
     */
    protected function getTypeAsString(ComplexType|Identifier|Name|null $type): ?string
    {
        return match (true) {
            $type === null => null,
            $type instanceof Identifier => $type->name,
            $type instanceof Name => $type->toString(),
            $type instanceof NullableType =>
                ($inner = $this->getTypeAsString($type->type)) !== null
                    ? '?' . $inner
                    : null,
            $type instanceof UnionType => implode('|', array_filter(
                array_map(fn($t) => $this->getTypeAsString($t), $type->types)
            )),
            $type instanceof IntersectionType => implode('&', array_filter(
                array_map(fn($t) => $this->getTypeAsString($t), $type->types)
            )),
            default => null,
        };
    }

    /**
     * Check if a subject string matches any of the given regex patterns.
     *
     * @param string $subject The string to test
     * @param array<string> $patterns Array of regex patterns
     * @return bool True if any pattern matches
     */
    protected function matchesAnyPattern(string $subject, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $subject) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize a class name by removing leading backslash.
     *
     * @param string $className
     * @return string
     */
    protected function normalizeClassName(string $className): string
    {
        return ltrim($className, '\\');
    }
}
