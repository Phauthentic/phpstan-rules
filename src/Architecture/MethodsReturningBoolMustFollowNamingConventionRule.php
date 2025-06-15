<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\BooleanType;
use PHPStan\Type\TypeWithClassName;

/**
 * Specification:
 * - Any class method that returns a boolean must follow the naming convention provided by the regex.
 *
 * @implements Rule<ClassMethod>
 */
class MethodsReturningBoolMustFollowNamingConventionRule implements Rule
{
    public function __construct(
        protected string $regex = '/^(is|has|can|should|was|will)[A-Z_]/'
    ) {
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * Skip constructors, destructors, and magic methods
     */
    private function isSkippableMethod(Node $node): bool
    {
        return $node->name->toString() === '__construct' ||
               $node->name->toString() === '__destruct' ||
               strpos($node->name->toString(), '__') === 0;
    }

    private function hasReturnType(Node $node): bool
    {
        return $node->returnType !== null;
    }

    private function hasBooleanReturnType(Node $node, Scope $scope): bool
    {
        // Get the method reflection
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return false;
        }

        if (!$classReflection->hasMethod($node->name->toString())) {
            return false;
        }

        $methodReflection = $classReflection->getMethod($node->name->toString(), $scope);

        $returnType = $methodReflection->getVariants()[0]->getReturnType();

        // Check if the return type is boolean
        if (!$returnType instanceof BooleanType) {
            return false;
        }

        return true;
    }

    /**
     * @param ClassMethod $node
     * @param Scope $scope
     * @return list<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (
            $this->isSkippableMethod($node) ||
            !$this->hasReturnType($node) ||
            !$this->hasBooleanReturnType($node, $scope)
        ) {
            return [];
        }

        $methodName = $node->name->toString();
        if (!preg_match($this->regex, $methodName)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Method %s::%s() returns boolean but does not follow naming convention (regex: %s).',
                    $scope->getClassReflection()->getName(),
                    $methodName,
                    $this->regex
                ))->line($node->getLine())->build()
            ];
        }

        return [];
    }
}
