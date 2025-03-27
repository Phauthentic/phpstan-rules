<?php

declare(strict_types=1);

namespace Phauthentic\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class ReadonlyClassRule implements Rule
{
    protected array $patterns;

    public function __construct(array $patterns)
    {
        $this->patterns = $patterns;
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Class_ || !isset($node->name)) {
            return [];
        }

        $className = $node->name->toString();
        $namespaceName = $scope->getNamespace() ?? '';
        $fullClassName = $namespaceName . '\\' . $className;

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $fullClassName) && !$node->isReadonly()) {
                return [
                    RuleErrorBuilder::message("Class {$fullClassName} must be readonly.")
                        ->build(),
                ];
            }
        }

        return [];
    }
}
