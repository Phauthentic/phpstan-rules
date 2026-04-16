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
use PhpParser\Node\Expr\BinaryOp;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Specification:
 *
 * - Reports `===` and `!==` when both operands are definitely `\DateTimeInterface` (identity
 *   comparison does not mean the same instant).
 * - If `patterns` is empty, the rule applies globally (any scope PHPStan analyzes).
 * - If `patterns` is non-empty, only class methods whose `Full\Class\Name::methodName` matches
 *   any pattern are checked (requires class and function reflections in scope).
 * - Does not report other operators (e.g. `==`, `<=>`).
 *
 * Unlike {@see \Phauthentic\PHPStanRules\CleanCode\ForbiddenElseStatementsRule}, an empty `patterns` list does not disable this rule;
 * it means “no scope filter” (global).
 *
 * @implements Rule<BinaryOp>
 */
class ForbiddenDateTimeComparisonRule implements Rule
{
    use ClassNameResolver;

    private const IDENTIFIER = 'phauthentic.architecture.forbiddenDateTimeComparison';

    private const ERROR_MESSAGE = 'Cannot compare DateTimeInterface values with %s: this compares object identity (whether both sides are the same in-memory PHP instance), not whether the two datetimes represent the same point in time. Use == / != for value comparison, or compare instants explicitly (e.g. getTimestamp(), format(), DateTimeImmutable::createFromInterface()).';

    /**
     * @param list<string> $patterns PCRE regexes matched against `Full\Class\Name::methodName`. Empty = global.
     */
    public function __construct(
        private array $patterns = [],
    ) {
    }

    public function getNodeType(): string
    {
        return BinaryOp::class;
    }

    /**
     * @param BinaryOp $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (
            !$node instanceof BinaryOp\Identical
            && !$node instanceof BinaryOp\NotIdentical
        ) {
            return [];
        }

        if (!$this->shouldAnalyzeScope($scope)) {
            return [];
        }

        $leftType = $scope->getType($node->left);
        $rightType = $scope->getType($node->right);
        $dateTimeType = new ObjectType(\DateTimeInterface::class);

        if (
            !$dateTimeType->isSuperTypeOf($leftType)->yes()
            || !$dateTimeType->isSuperTypeOf($rightType)->yes()
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                self::ERROR_MESSAGE,
                $node->getOperatorSigil()
            ))
                ->identifier(self::IDENTIFIER)
                ->line($node->getLine())
                ->build(),
        ];
    }

    private function shouldAnalyzeScope(Scope $scope): bool
    {
        if ($this->patterns === []) {
            return true;
        }

        $classReflection = $scope->getClassReflection();
        $functionReflection = $scope->getFunction();
        if ($classReflection === null || $functionReflection === null) {
            return false;
        }

        $fullName = $classReflection->getName() . '::' . $functionReflection->getName();

        return $this->matchesAnyPattern($fullName, $this->patterns);
    }
}
