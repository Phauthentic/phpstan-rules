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

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Specification:
 *
 * - Checks namespace declarations in PHP code.
 * - A (sub) namespace matching a given regex is not allowed to be declared.
 * - Reports an error if a forbidden namespace is declared.
 *
 * @implements Rule<Namespace_>
 */
class ForbiddenNamespacesRule implements Rule
{
    private const ERROR_MESSAGE = 'Namespace "%s" is forbidden and cannot be declared.';

    private const IDENTIFIER = 'phauthentic.architecture.forbiddenNamespaces';

    /**
     * @var array<string>
     * An array of regex patterns for forbidden namespaces.
     * e.g., ['#^App\\Infrastructure\\.*#', '#^App\\Legacy\\.*#']
     */
    private array $forbiddenNamespaces;

    /**
     * @param array<string> $forbiddenNamespaces
     */
    public function __construct(array $forbiddenNamespaces)
    {
        $this->forbiddenNamespaces = $forbiddenNamespaces;
    }

    public function getNodeType(): string
    {
        return Namespace_::class;
    }

    /**
     * @param Namespace_ $node
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $namespaceName = $node->name ? $node->name->toString() : '';

        // Empty namespace is allowed (global namespace)
        if ($namespaceName === '') {
            return [];
        }

        $errors = [];

        foreach ($this->forbiddenNamespaces as $forbiddenPattern) {
            if (preg_match($forbiddenPattern, $namespaceName)) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    self::ERROR_MESSAGE,
                    $namespaceName
                ))
                ->identifier(self::IDENTIFIER)
                ->line($node->getLine())
                ->build();

                // Break after first match to avoid duplicate errors
                break;
            }
        }

        return $errors;
    }
}
