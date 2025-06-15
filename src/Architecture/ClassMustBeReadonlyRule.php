<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 * @implements Rule<Class_>
 */
class ClassMustBeReadonlyRule implements Rule
{
    private const ERROR_MESSAGE = 'Class %s must be readonly.';

    private const IDENTIFIER = 'phauthentic.architecture.classMustBeReadonly';

    /**
     * @var string[]
     */
    protected array $patterns;

    /**
     * @param string[] $patterns
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
            if (preg_match($pattern, $fullClassName) && !$node->isReadonly()) {
                return [
                    RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $fullClassName))
                        ->identifier(self::IDENTIFIER)
                        ->build(),
                ];
            }
        }

        return [];
    }
}
