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

namespace Phauthentic\PHPStanRules\CleanCode;

use Phauthentic\PHPStanRules\PhpParser\ParentNodeAttributeVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Specification:
 *
 * - Checks the nesting level of control structures (if, else, elseif, try, catch).
 * - Reports an error if the nesting level exceeds the configured maximum.
 *
 * @implements Rule<Node>
 */
class ControlStructureNestingRule implements Rule
{
    private const ERROR_MESSAGE = 'Nesting level of %d exceeded. Maximum allowed is %d.';

    private const IDENTIFIER = 'phauthentic.cleancode.controlStructureNesting';

    private int $maxNestingLevel;

    public function __construct(int $maxNestingLevel)
    {
        $this->maxNestingLevel = $maxNestingLevel;
    }

    public function getNodeType(): string
    {
        return Node::class; // Process all nodes
    }

    /**
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $this->setParentAttributesToEnableCorrectNestingDetection($node);

        if (!$this->nodeIsAControlStructure($node)) {
            return [];
        }

        /** @var list<IdentifierRuleError> $errors */
        $errors = [];
        $nestingLevel = $this->getNestingLevel($node);

        if ($nestingLevel > $this->maxNestingLevel) {
            /** @var Else_|If_|Catch_|ElseIf_|TryCatch $controlNode */
            $controlNode = $node;
            $errors = $this->addError($nestingLevel, $controlNode, $errors);
        }

        return $errors;
    }

    private function nodeIsAControlStructure(Node $node): bool
    {
        return $node instanceof If_ ||
            $node instanceof Else_ ||
            $node instanceof ElseIf_ ||
            $node instanceof TryCatch ||
            $node instanceof Catch_;
    }

    private function getNestingLevel(Node $node, int $currentLevel = 1): int
    {
        /** @var Node|null $parent */
        $parent = $node->getAttribute(ParentNodeAttributeVisitor::ATTRIBUTE_NAME);
        if ($parent !== null && $this->nodeIsAControlStructure($parent)) {
            return $this->getNestingLevel($parent, $currentLevel + 1);
        }

        return $currentLevel;
    }

    /**
     * @param Node $node
     * @return void
     */
    public function setParentAttributesToEnableCorrectNestingDetection(Node $node): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->createNodeVisitor());
        $traverser->traverse([$node]);
    }

    /**
     * @param list<IdentifierRuleError> $errors
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    public function addError(int $nestingLevel, Else_|If_|Catch_|ElseIf_|TryCatch $node, array $errors): array
    {
        $errorMessage = sprintf(
            self::ERROR_MESSAGE,
            $nestingLevel,
            $this->maxNestingLevel
        );

        $errors[] = RuleErrorBuilder::message($errorMessage)
            ->line($node->getLine())
            ->identifier(self::IDENTIFIER)
            ->build();

        return $errors;
    }

    public function createNodeVisitor(): ParentNodeAttributeVisitor
    {
        return new ParentNodeAttributeVisitor();
    }
}
