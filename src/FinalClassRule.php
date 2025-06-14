<?php

declare(strict_types=1);

namespace Phauthentic\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Class_>
 */
class FinalClassRule implements Rule
{
    private const ERROR_MESSAGE = 'Class %s must be final.';

    /**
     * @var array<string> An array of regex patterns to match against class names.
     * e.g., ['#^App\\Domain\\.*#', '#^App\\Service\\.*#']
     */
    protected array $patterns;

    /**
     * @param array<string> $patterns An array of regex patterns to match against class names.
     * Each pattern should be a valid PCRE regex.
     */
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
            if (preg_match($pattern, $fullClassName) && !$node->isFinal()) {
                return [
                    RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $fullClassName))
                        ->build(),
                ];
            }
        }

        return [];
    }
}
