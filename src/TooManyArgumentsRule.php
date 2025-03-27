<?php

declare(strict_types=1);

namespace Phauthentic\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A configurable rule to check the number of arguments in a method.
 */
class TooManyArgumentsRule implements Rule
{
    private int $maxArguments;
    private array $patterns;

    public function __construct(int $maxArguments, array $patterns = [])
    {
        $this->maxArguments = $maxArguments;
        $this->patterns = $patterns;
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    private function isClassMethod(Node $node): bool
    {
        return $node instanceof ClassMethod;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isClassMethod($node)
            || !$this->matchesPattern($scope)
            || !$this->argumentCountIsExceeded($node)
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Method %s::%s has too many arguments (%d). Maximum allowed is %d.',
                    $scope->getClassReflection()->getName(),
                    $node->name->toString(),
                    count($node->params),
                    $this->maxArguments
                )
            )->build()
        ];
    }

    private function argumentCountIsExceeded(Node $node): bool
    {
        $numArguments = count($node->params);

        return $numArguments > $this->maxArguments;
    }

    private function matchesPattern(Scope $scope): bool
    {
        $className = $scope->getClassReflection()->getName();

        if (empty($this->patterns)) {
            return true;
        }

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $className)) {
                return true;
            }
        }

        return false;
    }
}
