<?php

declare(strict_types=1);

namespace Phauthentic\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * A configurable rule to check the number of arguments in a method.
 *
 * @implements Rule<ClassMethod>
 */
class TooManyArgumentsRule implements Rule
{
    private const ERROR_MESSAGE = 'Method %s::%s has too many arguments (%d). Maximum allowed is %d.';

    private const IDENTIFIER = 'phauthentic.cleancode.tooManyArguments';

    private int $maxArguments;

    /**
     * @var string[]
     */
    private array $patterns;

    /**
     * @param string[] $patterns
     */
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

    /**
     * Processes the node and checks if it exceeds the maximum number of arguments.
     *
     * @param Node $node The node to process.
     * @param Scope $scope The scope of the node.
     * @return RuleError[] An array of rule errors if any.
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (
            !$this->isClassMethod($node)
            || !$this->matchesPattern($scope)
            || !$this->argumentCountIsExceeded($node)
        ) {
            return [];
        }

        $message = $this->buildErrorMessage($scope, $node);

        return [
            RuleErrorBuilder::message($message)
            ->identifier(self::IDENTIFIER)
            ->build()
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

    /**
     * @param Scope $scope
     * @param Node $node
     * @return string
     */
    public function buildErrorMessage(Scope $scope, Node $node): string
    {
        return sprintf(
            self::ERROR_MESSAGE,
            $scope->getClassReflection()->getName(),
            $node->name->toString(),
            count($node->params),
            $this->maxArguments
        );
    }
}
