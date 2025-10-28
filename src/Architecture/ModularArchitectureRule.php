<?php

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
 * A PHPStan rule to enforce modular hexagonal architecture constraints.
 *
 * This rule enforces:
 * 1. Intra-module layer dependencies (Domain, Application, Infrastructure, Presentation)
 * 2. Cross-module dependencies (only facades and DTOs allowed)
 *
 * Specification:
 * - Domain layer cannot import from Application, Infrastructure, or Presentation
 * - Application layer can import Domain; cannot import Infrastructure or Presentation
 * - Infrastructure layer can import Domain and Application; cannot import Presentation
 * - Presentation layer can import Application and Domain; cannot import Infrastructure
 * - Cross-module imports only allowed for: Facade.php, FacadeInterface.php, *Input.php, *Result.php
 *
 * Note: Circular dependency detection is handled by CircularModuleDependencyRule.
 *
 * @implements Rule<Use_>
 */
class ModularArchitectureRule implements Rule
{
    private const IDENTIFIER_LAYER = 'phauthentic.architecture.modular.layer';
    private const IDENTIFIER_CROSS_MODULE = 'phauthentic.architecture.modular.crossModule';

    private const LAYER_DOMAIN = 'Domain';
    private const LAYER_APPLICATION = 'Application';
    private const LAYER_INFRASTRUCTURE = 'Infrastructure';
    private const LAYER_PRESENTATION = 'Presentation';

    /**
     * @var array<string, array<string>> Layer dependency rules (layer => allowed dependencies)
     */
    private array $layerDependencies;

    /**
     * @var array<string> Regex patterns for allowed cross-module imports
     */
    private array $allowedCrossModulePatterns;

    /**
     * @param string $baseNamespace The base namespace for capabilities (e.g., 'App\\Capability')
     * @param array<string, array<string>>|null $layerDependencies Custom layer dependency rules.
     *        Format: ['LayerName' => ['AllowedLayer1', 'AllowedLayer2']]
     *        If null, uses default hexagonal architecture rules.
     * @param array<string> $allowedCrossModulePatterns Regex patterns for class names that can be imported cross-module.
     *        Example: ['/Facade$/', '/FacadeInterface$/', '/Input$/', '/Result$/']
     */
    public function __construct(
        private string $baseNamespace,
        ?array $layerDependencies = null,
        array $allowedCrossModulePatterns = []
    ) {
        $this->layerDependencies = $layerDependencies ?? $this->getDefaultLayerDependencies();
        $this->allowedCrossModulePatterns = $allowedCrossModulePatterns;
    }

    /**
     * Get default layer dependency rules for hexagonal architecture
     *
     * @return array<string, array<string>>
     */
    private function getDefaultLayerDependencies(): array
    {
        return [
            self::LAYER_DOMAIN => [],
            self::LAYER_APPLICATION => [self::LAYER_DOMAIN],
            self::LAYER_INFRASTRUCTURE => [self::LAYER_DOMAIN, self::LAYER_APPLICATION],
            self::LAYER_PRESENTATION => [self::LAYER_APPLICATION],
        ];
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

        $sourceModuleInfo = $this->parseModuleInfo($currentNamespace);
        if ($sourceModuleInfo === null) {
            return [];
        }

        $errors = [];

        foreach ($node->uses as $use) {
            $useErrors = $this->processUseStatement($use, $sourceModuleInfo);
            $errors = array_merge($errors, $useErrors);
        }

        return $errors;
    }

    /**
     * Process a single use statement and return any errors
     *
     * @param \PhpParser\Node\Stmt\UseUse $use
     * @param array{module: string, layer: string|null, fullNamespace: string} $sourceModuleInfo
     * @return array<RuleError>
     */
    private function processUseStatement($use, array $sourceModuleInfo): array
    {
        $usedClassName = $use->name->toString();

        // Check if the imported class is also from modular architecture
        if (!$this->isModularNamespace($usedClassName)) {
            return [];
        }

        $targetModuleInfo = $this->parseModuleInfo($usedClassName);
        if ($targetModuleInfo === null) {
            return [];
        }

        // Check if this is a same-module or cross-module dependency
        if ($sourceModuleInfo['module'] === $targetModuleInfo['module']) {
            return $this->validateIntraModuleDependency(
                $sourceModuleInfo,
                $targetModuleInfo,
                $usedClassName,
                $use->getStartLine()
            );
        }

        return $this->validateCrossModuleDependencyWrapper(
            $sourceModuleInfo,
            $targetModuleInfo,
            $usedClassName,
            $use->getStartLine()
        );
    }

    /**
     * Validate intra-module (same module) dependency
     *
     * @param array{module: string, layer: string|null, fullNamespace: string} $sourceModuleInfo
     * @param array{module: string, layer: string|null, fullNamespace: string} $targetModuleInfo
     * @return array<RuleError>
     */
    private function validateIntraModuleDependency(
        array $sourceModuleInfo,
        array $targetModuleInfo,
        string $usedClassName,
        int $line
    ): array {
        $layerError = $this->validateLayerDependency(
            $sourceModuleInfo,
            $targetModuleInfo,
            $usedClassName,
            $line
        );

        return $layerError !== null ? [$layerError] : [];
    }

    /**
     * Validate cross-module dependency
     *
     * @param array{module: string, layer: string|null, fullNamespace: string} $sourceModuleInfo
     * @param array{module: string, layer: string|null, fullNamespace: string} $targetModuleInfo
     * @return array<RuleError>
     */
    private function validateCrossModuleDependencyWrapper(
        array $sourceModuleInfo,
        array $targetModuleInfo,
        string $usedClassName,
        int $line
    ): array {
        $crossModuleError = $this->validateCrossModuleDependency(
            $sourceModuleInfo,
            $targetModuleInfo,
            $usedClassName,
            $line
        );

        return $crossModuleError !== null ? [$crossModuleError] : [];
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
     * Parse module information from a namespace
     *
     * @return array{module: string, layer: string|null, fullNamespace: string}|null
     */
    private function parseModuleInfo(string $namespace): ?array
    {
        $escapedBase = str_replace('\\', '\\\\', $this->baseNamespace);
        
        // Build dynamic pattern from configured layer names
        $layerNames = array_keys($this->layerDependencies);
        $layerPattern = implode('|', array_map('preg_quote', $layerNames));
        
        // Match: BaseNamespace\ModuleName[\Layer[\...]]
        // Captures module name and optionally layer name
        $pattern = '/^' . $escapedBase . '\\\\([^\\\\]+)(?:\\\\(' . $layerPattern . ')(?:\\\\.*)?|\\\\.*)?$/';
        
        if (preg_match($pattern, $namespace, $matches)) {
            return [
                'module' => $matches[1],
                'layer' => $matches[2] ?? null,
                'fullNamespace' => $namespace
            ];
        }

        return null;
    }

    /**
     * Validate intra-module layer dependencies
     */
    private function validateLayerDependency(
        array $sourceModuleInfo,
        array $targetModuleInfo,
        string $usedClassName,
        int $line
    ): ?RuleError {
        $sourceLayer = $sourceModuleInfo['layer'];
        $targetLayer = $targetModuleInfo['layer'];

        // If either layer is null (root level), allow it
        if ($sourceLayer === null || $targetLayer === null) {
            return null;
        }

        // Allow a layer to import from itself
        if ($sourceLayer === $targetLayer) {
            return null;
        }

        // Check if the source layer is configured
        if (!isset($this->layerDependencies[$sourceLayer])) {
            return null;
        }

        // Check if the dependency is allowed
        if (!in_array($targetLayer, $this->layerDependencies[$sourceLayer], true)) {
            return RuleErrorBuilder::message(sprintf(
                'Layer violation: %s layer cannot depend on %s layer. Class in `%s` cannot import `%s`.',
                $sourceLayer,
                $targetLayer,
                $sourceModuleInfo['fullNamespace'],
                $usedClassName
            ))
            ->identifier(self::IDENTIFIER_LAYER)
            ->line($line)
            ->build();
        }

        return null;
    }

    /**
     * Validate cross-module dependencies (only facades and DTOs allowed)
     */
    private function validateCrossModuleDependency(
        array $sourceModuleInfo,
        array $targetModuleInfo,
        string $usedClassName,
        int $line
    ): ?RuleError {
        // Check if it's an allowed cross-module import using the full class name
        $isAllowed = $this->isAllowedCrossModuleImport($usedClassName);

        if (!$isAllowed) {
            return RuleErrorBuilder::message(sprintf(
                'Cross-module violation: Module `%s` is not allowed to import `%s` from module `%s`.',
                $sourceModuleInfo['module'],
                $usedClassName,
                $targetModuleInfo['module']
            ))
            ->identifier(self::IDENTIFIER_CROSS_MODULE)
            ->line($line)
            ->build();
        }

        return null;
    }

    /**
     * Check if a class is allowed for cross-module import
     * Matches against the fully qualified class name
     */
    private function isAllowedCrossModuleImport(string $fullyQualifiedClassName): bool
    {
        foreach ($this->allowedCrossModulePatterns as $pattern) {
            if (preg_match($pattern, $fullyQualifiedClassName)) {
                return true;
            }
        }

        return false;
    }
}

