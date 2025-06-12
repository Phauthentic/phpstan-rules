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
 */
class NamespaceClassPatternRule implements Rule
{
    private const ERROR_MESSAGE = 'Class %s in namespace %s does not match any of the required patterns:';

    /** @var array{namespace: string, classPatterns: string[]}[] */
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
        $errors = [];

        if (!$node instanceof Namespace_) {
            return [];
        }

        $namespaceName = $node->name ? $node->name->toString() : '';

        foreach ($this->namespaceClassPatterns as $config) {
            if (preg_match($config['namespace'], $namespaceName)) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof Class_) {
                        $className = $stmt->name ? $stmt->name->toString() : '';
                        $matches = false;
                        foreach ($config['classPatterns'] as $pattern) {
                            if (preg_match($pattern, $className)) {
                                $matches = true;
                                break;
                            }
                        }

                        if (!$matches) {
                            $fqcn = $namespaceName ? $namespaceName . '\\' . $className : $className;
                            $patterns = [];
                            foreach ($config['classPatterns'] as $pattern) {
                                $patterns[] = ' - ' . $pattern;
                            }
                            $errors[] = RuleErrorBuilder::message(
                                sprintf(self::ERROR_MESSAGE, $fqcn, $namespaceName) . PHP_EOL . implode(PHP_EOL, $patterns)
                            )->line($stmt->getLine())->build();
                        }
                    }
                }
            }
        }

        return $errors;
    }
}
