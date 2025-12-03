<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Specification:
 *
 * - Checks if a class matches a given regex pattern.
 * - Checks if the class is declared as final.
 *
 * @implements Rule<Class_>
 */
class ClassMustBeFinalRule implements Rule
{
    private const ERROR_MESSAGE = 'Class %s must be final.';

    private const IDENTIFIER = 'phauthentic.architecture.classMustBeFinal';

    /**
     * @param array<string> $patterns An array of regex patterns to match against class names.
     * Each pattern should be a valid PCRE regex.
     */
    public function __construct(
        protected array $patterns,
        protected bool $ignoreAbstractClasses = true
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!isset($node->name)) {
            return [];
        }

        // Skip abstract classes if configured to ignore them
        if ($this->ignoreAbstractClasses && $node->isAbstract()) {
            return [];
        }

        $className = $node->name->toString();
        $namespaceName = $scope->getNamespace() ?? '';
        $fullClassName = $namespaceName . '\\' . $className;

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $fullClassName) && !$node->isFinal()) {
                return [$this->buildRuleError($fullClassName)];
            }
        }

        return [];
    }

    private function buildRuleError(string $fullClassName): \PHPStan\Rules\RuleError
    {
        return RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $fullClassName))
            ->identifier(self::IDENTIFIER)
            ->build();
    }
}
