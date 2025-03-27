<?php

declare(strict_types=1);

namespace Phauthentic\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A PHPStan rule to enforce dependency constraints between namespaces.
 * 
 * This rule checks the `use` statements in your PHP code and ensures that
 * certain namespaces do not depend on other namespaces as specified in the
 * configuration.
 */
class DependencyConstraintsRule implements Rule
{
    private array $namespaceDependencies;

    public function __construct(array $namespaceDependencies)
    {
        $this->namespaceDependencies = $namespaceDependencies;
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        if ($node instanceof Use_) {
            $errors = array_merge($errors, $this->checkUseDependencies($node, $scope));
        }

        return $errors;
    }

    private function checkUseDependencies(Use_ $node, Scope $scope): array
    {
        $errors = [];
        foreach ($node->uses as $use) {
            $useNamespace = $use->name->toString();
            foreach ($this->namespaceDependencies as $dependencyPatterns) {
                $errors = $this->checkForDependencyViolations($dependencyPatterns, $useNamespace, $scope, $node, $errors);
            }
        }

        return $errors;
    }

    public function checkForDependencyViolations(mixed $dependencyPatterns, string $useNamespace, Scope $scope, Use_ $node, array $errors): array
    {
        foreach ($dependencyPatterns as $dependencyPattern) {
            if (preg_match($dependencyPattern, $useNamespace)) {
                $errors[] = RuleErrorBuilder::message("Class {$scope->getNamespace()} has a dependency on {$useNamespace}, which is not allowed.")
                    ->line($node->getLine())
                    ->build();
            }
        }
        return $errors;
    }
}
