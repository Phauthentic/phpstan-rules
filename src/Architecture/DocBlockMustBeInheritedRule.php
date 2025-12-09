<?php

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author    Florian Krämer
 * @link      https://github.com/Phauthentic
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 *
 * - Checks if the classname plus method name matches a given regex pattern.
 * - Enforces that matched methods must have a docblock with @inheritDoc or @inheritdoc.
 * - Methods are matched using FQCN::methodName format with regex patterns.
 *
 * @implements Rule<Class_>
 */
class DocBlockMustBeInheritedRule implements Rule
{
    private const ERROR_MESSAGE_MISSING_DOCBLOCK = 'Method %s must have a docblock with @inheritDoc or @inheritdoc.';
    private const ERROR_MESSAGE_MISSING_INHERITDOC = 'Method %s docblock must contain @inheritDoc or @inheritdoc.';
    private const IDENTIFIER = 'phauthentic.architecture.docBlockMustBeInherited';

    /**
     * @param array<string> $patterns An array of regex patterns to match against FQCN::methodName.
     * Each pattern should be a valid PCRE regex.
     */
    public function __construct(
        protected array $patterns = []
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @param Scope $scope
     * @return array<\PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $className = $node->name ? $node->name->toString() : '';
        $namespaceName = $scope->getNamespace() ?? '';
        $fullClassName = $namespaceName !== '' ? $namespaceName . '\\' . $className : $className;

        foreach ($node->getMethods() as $method) {
            $methodName = $method->name->toString();
            $fullMethodName = $fullClassName . '::' . $methodName;

            if (!$this->matchesPatterns($fullMethodName)) {
                continue;
            }

            $docComment = $method->getDocComment();

            if ($docComment === null) {
                $errors[] = $this->buildMissingDocblockError($fullMethodName, $method->getLine());
                continue;
            }

            if (!$this->hasInheritDoc($docComment)) {
                $errors[] = $this->buildMissingInheritDocError($fullMethodName, $method->getLine());
            }
        }

        return $errors;
    }

    /**
     * Check if the target matches any of the configured patterns
     */
    private function matchesPatterns(string $target): bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $target)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if docblock contains @inheritDoc or @inheritdoc as an annotation
     */
    private function hasInheritDoc(Doc $docComment): bool
    {
        $text = $docComment->getText();

        // Match @inheritDoc or @inheritdoc as an annotation (not just mentioned in text)
        // It should appear after * and optional whitespace at the start of a line
        return (bool) preg_match('/^\s*\*\s*@inheritdoc\b/im', $text);
    }

    /**
     * @return \PHPStan\Rules\RuleError
     */
    private function buildMissingDocblockError(string $fullMethodName, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_MISSING_DOCBLOCK,
                $fullMethodName
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }

    /**
     * @return \PHPStan\Rules\RuleError
     */
    private function buildMissingInheritDocError(string $fullMethodName, int $line)
    {
        return RuleErrorBuilder::message(
            sprintf(
                self::ERROR_MESSAGE_MISSING_INHERITDOC,
                $fullMethodName
            )
        )
        ->identifier(self::IDENTIFIER)
        ->line($line)
        ->build();
    }
}
