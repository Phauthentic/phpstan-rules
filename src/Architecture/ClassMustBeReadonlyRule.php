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
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 *
 * - Checks if a class matches a given regex pattern.
 * - A matching class must be declared as readonly.
 *
 * @implements Rule<Class_>
 */
class ClassMustBeReadonlyRule implements Rule
{
    use ClassNameResolver;

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

    /**
     * @param Class_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $fullClassName = $this->resolveFullClassName($node, $scope);
        if ($fullClassName === null) {
            return [];
        }

        if ($this->matchesAnyPattern($fullClassName, $this->patterns) && !$node->isReadonly()) {
            return [
                RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $fullClassName))
                    ->identifier(self::IDENTIFIER)
                    ->build(),
            ];
        }

        return [];
    }
}
