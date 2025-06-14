<?php

declare(strict_types=1);

namespace Phauthentic\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * A PHPStan rule to enforce dependency constraints between namespaces.
 *
 * This rule checks the `use` statements in your PHP code and ensures that
 * certain namespaces do not depend on other namespaces as specified in the
 * configuration.
 */
class DependencyConstraintsRule implements Rule
{
    private const ERROR_MESSAGE = 'Dependency violation: A class in namespace `%s` is not allowed to depend on `%s`.';

    private const IDENTIFIER = 'phauthentic.architecture.dependencyConstraints';

    /**
     * @var array<string, array<string>>
     * An array where the key is a regex for the source namespace and the value is
     * an array of regexes for disallowed dependency namespaces.
     * e.g., ['#^App\\Domain\\.*#' => ['#^App\\Infrastructure\\.*#']]
     */
    private array $forbiddenDependencies;

    /**
     * @param array<string, array<string>> $forbiddenDependencies
     */
    public function __construct(array $forbiddenDependencies)
    {
        $this->forbiddenDependencies = $forbiddenDependencies;
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

        $errors = [];

        foreach ($this->forbiddenDependencies as $sourceNamespacePattern => $disallowedDependencyPatterns) {
            if (!preg_match($sourceNamespacePattern, $currentNamespace)) {
                continue;
            }

            $errors = $this->validateUseStatements($node, $disallowedDependencyPatterns, $currentNamespace, $errors);
        }

        return $errors;
    }

    /**
     * @param Node $node
     * @param array<string> $disallowedDependencyPatterns
     * @param string $currentNamespace
     * @param array<RuleError> $errors
     * @return array<RuleError>
     * @throws ShouldNotHappenException
     */
    public function validateUseStatements(Node $node, array $disallowedDependencyPatterns, string $currentNamespace, array $errors): array
    {
        foreach ($node->uses as $use) {
            $usedClassName = $use->name->toString();
            foreach ($disallowedDependencyPatterns as $disallowedPattern) {
                if (preg_match($disallowedPattern, $usedClassName)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        self::ERROR_MESSAGE,
                        $currentNamespace,
                        $usedClassName
                    ))
                    ->identifier(self::IDENTIFIER)
                    ->line($use->getStartLine())
                    ->build();
                }
            }
        }

        return $errors;
    }
}
