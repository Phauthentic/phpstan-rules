<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\PhpParser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Node visitor that sets parent node attributes on child nodes.
 *
 * This enables traversing up the AST tree to determine nesting levels.
 */
class ParentNodeAttributeVisitor extends NodeVisitorAbstract
{
    /**
     * @return int|Node|null
     */
    public function enterNode(Node $node)
    {
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;
            $this->setParentAttributeForSubNode($subNode, $node);
        }

        return null;
    }

    /**
     * @param mixed $subNode
     * @param Node $parentNode
     */
    private function setParentAttributeForSubNode($subNode, Node $parentNode): void
    {
        if (is_array($subNode)) {
            $this->setParentAttributeForArrayOfNodes($subNode, $parentNode);
            return;
        }

        if ($subNode instanceof Node) {
            $subNode->setAttribute('parent', $parentNode);
        }
    }

    /**
     * @param array<mixed> $nodes
     * @param Node $parentNode
     */
    private function setParentAttributeForArrayOfNodes(array $nodes, Node $parentNode): void
    {
        foreach ($nodes as $childNode) {
            if ($childNode instanceof Node) {
                $childNode->setAttribute('parent', $parentNode);
            }
        }
    }
}

