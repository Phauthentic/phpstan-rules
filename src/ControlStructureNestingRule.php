<?php

declare(strict_types=1);

namespace Phauthentic\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A rule to check the nesting level of control structures (if, else, elseif, try, catch).
 *
 * @implements Rule<Node>
 */
class ControlStructureNestingRule implements Rule
{
    private const ERROR_MESSAGE = 'Nesting level of %d exceeded. Maximum allowed is %d.';

    private int $maxNestingLevel;

    public function __construct(int $maxNestingLevel)
    {
        $this->maxNestingLevel = $maxNestingLevel;
    }

    public function getNodeType(): string
    {
        return Node::class; // Process all nodes
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $this->setParentAttributesToEnableCorrectNestingDetection($node);

        if (!$this->nodeIsAControlStructure($node)) {
            return [];
        }

        $errors = [];
        $nestingLevel = $this->getNestingLevel($node);
        if ($nestingLevel > $this->maxNestingLevel) {
            $errors = $this->addError($nestingLevel, $node, $errors);
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
        $parent = $node->getAttribute('parent');
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
     * @param int $nestingLevel
     * @param Else_|If_|Catch_|ElseIf_|TryCatch $node
     * @param array $errors
     * @return array
     */
    public function addError(int $nestingLevel, Else_|If_|Catch_|ElseIf_|TryCatch $node, array $errors): array
    {
        $errorMessage = sprintf(
            self::ERROR_MESSAGE,
            $nestingLevel,
            $this->maxNestingLevel
        );

        $errors[] = RuleErrorBuilder::message($errorMessage)->line($node->getLine())->build();

        return $errors;
    }

    public function createNodeVisitor(): object
    {
        return new class extends NodeVisitorAbstract {
            public function enterNode(Node $node)
            {
                foreach ($node->getSubNodeNames() as $subNodeName) {
                    $subNode = $node->$subNodeName;
                    if (is_array($subNode)) {
                        foreach ($subNode as $childNode) {
                            if ($childNode instanceof Node) {
                                $childNode->setAttribute('parent', $node);
                            }
                        }
                        continue;
                    }

                    if ($subNode instanceof Node) {
                        $subNode->setAttribute('parent', $node);
                    }
                }
            }
        };
    }
}
