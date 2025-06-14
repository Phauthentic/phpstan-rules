<?php

declare(strict_types=1);

namespace Phauthentic\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * PHPStan rule to ensure that classes inside namespaces matching a given regex
 * must have names matching at least one of the provided patterns.
 *
 * @implements Rule<Namespace_>
 */
class NamespaceClassPatternRule implements Rule
{
    /**
     * @var array{namespace: string, classPatterns: string[]}[]
     */
    private array $namespaceClassPatterns;

    /**
     * @param array{namespace: string, classPatterns: string[]}[] $namespaceClassPatterns
     */
    public function __construct(array $namespaceClassPatterns)
    {
        $this->namespaceClassPatterns = $namespaceClassPatterns;
    }

    public function getNodeType(): string
    {
        return Namespace_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Namespace_) {
            return [];
        }

        $namespaceName = $node->name ? $node->name->toString() : '';
        $errors = [];

        foreach ($this->namespaceClassPatterns as $config) {
            if (!$this->namespaceMatches($namespaceName, $config['namespace'])) {
                continue;
            }

            foreach ($node->stmts as $stmt) {
                if (!$stmt instanceof Class_) {
                    continue;
                }

                $className = $stmt->name ? $stmt->name->toString() : '';
                if (!$this->classNameMatches($className, $config['classPatterns'])) {
                    $errors = $this->buildRuleError($namespaceName, $className, $config['classPatterns'], $stmt, $errors);
                }
            }
        }

        return $errors;
    }

    private function namespaceMatches(string $namespace, string $namespacePattern): bool
    {
        return preg_match($namespacePattern, $namespace) === 1;
    }

    /**
     * @param string[] $classPatterns
     */
    private function classNameMatches(string $className, array $classPatterns): bool
    {
        foreach ($classPatterns as $pattern) {
            if (preg_match($pattern, $className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $patterns
     */
    private function buildErrorMessage(string $fqcn, string $namespace, array $patterns): string
    {
        $lines = [
            sprintf('Class %s in namespace %s does not match any of the required patterns:', $fqcn, $namespace),
        ];

        foreach ($patterns as $pattern) {
            $lines[] = ' - ' . $pattern;
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param string $namespaceName
     * @param string $className
     * @param $classPatterns
     * @param Class_ $stmt
     * @param array $errors
     * @return array
     */
    public function buildRuleError(string $namespaceName, string $className, $classPatterns, Class_ $stmt, array $errors): array
    {
        $fqcn = $namespaceName ? $namespaceName . '\\' . $className : $className;
        $errors[] = RuleErrorBuilder::message(
            $this->buildErrorMessage($fqcn, $namespaceName, $classPatterns)
        )->line($stmt->getLine())->build();

        return $errors;
    }
}
