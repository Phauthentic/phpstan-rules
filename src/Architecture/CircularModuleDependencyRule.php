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
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * A PHPStan rule to detect circular dependencies between modules.
 *
 * This rule tracks dependencies between modules and reports when circular
 * dependencies are detected (e.g., Module A → Module B → Module C → Module A).
 *
 * @implements Rule<Use_>
 */
class CircularModuleDependencyRule implements Rule
{
    private const IDENTIFIER_CIRCULAR = 'phauthentic.architecture.circular';

    /**
     * @var array<string, array<string>> Track module dependencies for circular detection
     */
    private static array $moduleDependencies = [];

    /**
     * @param string $baseNamespace The base namespace for capabilities (e.g., 'App\\Capability')
     */
    public function __construct(
        private string $baseNamespace
    ) {
    }

    public function getNodeType(): string
    {
        return Use_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $currentNamespace = $scope->getNamespace();
        if ($currentNamespace === null) {
            return [];
        }

        // Check if this is within our modular architecture
        if (!$this->isModularNamespace($currentNamespace)) {
            return [];
        }

        $sourceModule = $this->extractModuleName($currentNamespace);
        if ($sourceModule === null) {
            return [];
        }

        $errors = [];

        foreach ($node->uses as $use) {
            $usedClassName = $use->name->toString();

            // Check if the imported class is also from modular architecture
            if (!$this->isModularNamespace($usedClassName)) {
                continue;
            }

            $targetModule = $this->extractModuleName($usedClassName);
            if ($targetModule === null || $sourceModule === $targetModule) {
                continue;
            }

            // Track the dependency
            $this->trackDependency($sourceModule, $targetModule);

            // Check for circular dependencies
            $circularError = $this->detectCircularDependency(
                $sourceModule,
                $targetModule,
                $use->getStartLine()
            );
            if ($circularError !== null) {
                $errors[] = $circularError;
            }
        }

        return $errors;
    }

    /**
     * Check if a namespace belongs to the modular architecture
     */
    private function isModularNamespace(string $namespace): bool
    {
        $escapedBase = str_replace('\\', '\\\\', $this->baseNamespace);
        return preg_match('/^' . $escapedBase . '\\\\/', $namespace) === 1;
    }

    /**
     * Extract module name from a namespace
     *
     * @return string|null The module name or null if not a valid module namespace
     */
    private function extractModuleName(string $namespace): ?string
    {
        $escapedBase = str_replace('\\', '\\\\', $this->baseNamespace);
        $pattern = '/^' . $escapedBase . '\\\\([^\\\\]+)/';

        if (preg_match($pattern, $namespace, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Track a dependency between modules
     */
    private function trackDependency(string $sourceModule, string $targetModule): void
    {
        if (!isset(self::$moduleDependencies[$sourceModule])) {
            self::$moduleDependencies[$sourceModule] = [];
        }

        if (!in_array($targetModule, self::$moduleDependencies[$sourceModule], true)) {
            self::$moduleDependencies[$sourceModule][] = $targetModule;
        }
    }

    /**
     * Detect circular dependencies using DFS
     */
    private function detectCircularDependency(
        string $sourceModule,
        string $targetModule,
        int $line
    ): ?RuleError {
        // Check if adding this dependency would create a cycle
        // We're adding: sourceModule → targetModule
        // Check if there's already a path: targetModule → ... → sourceModule
        $cycle = $this->findCycle($targetModule, $sourceModule, []);

        if ($cycle !== null) {
            // Complete the cycle by adding the source module at the start
            array_unshift($cycle, $sourceModule);
            $cycleString = implode(' → ', $cycle);
            return RuleErrorBuilder::message(sprintf(
                'Circular dependency detected: %s',
                $cycleString
            ))
            ->identifier(self::IDENTIFIER_CIRCULAR)
            ->line($line)
            ->build();
        }

        return null;
    }

    /**
     * Find a cycle using DFS
     *
     * @param array<string> $path
     * @return array<string>|null The cycle path if found, null otherwise
     */
    private function findCycle(string $currentModule, string $targetModule, array $path): ?array
    {
        $path[] = $currentModule;

        // If we've reached the target, we found a cycle
        if ($currentModule === $targetModule) {
            return $path;
        }

        // Explore dependencies
        $dependencies = self::$moduleDependencies[$currentModule] ?? [];
        foreach ($dependencies as $dependency) {
            // Avoid infinite loops in already visited paths
            if (in_array($dependency, $path, true)) {
                continue;
            }

            $cycle = $this->findCycle($dependency, $targetModule, $path);
            if ($cycle !== null) {
                return $cycle;
            }
        }

        return null;
    }

    /**
     * Reset the dependency tracking (useful for testing)
     */
    public static function resetDependencyTracking(): void
    {
        self::$moduleDependencies = [];
    }
}
