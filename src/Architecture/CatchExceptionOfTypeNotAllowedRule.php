<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Catch_>
 */
class CatchExceptionOfTypeNotAllowedRule implements Rule
{
    private const ERROR_MESSAGE = 'Catching exception of type %s is not allowed.';

    private const IDENTIFIER = 'phauthentic.architecture.catchExceptionOfTypeNotAllowed';

    /**
     * @var array<string> An array of exception class names that are not allowed to be caught.
     * e.g., ['Exception', 'Error', 'Throwable']
     */
    private array $forbiddenExceptionTypes;

    /**
     * @param array<string> $forbiddenExceptionTypes An array of exception class names that are not allowed to be caught.
     */
    public function __construct(array $forbiddenExceptionTypes)
    {
        $this->forbiddenExceptionTypes = $forbiddenExceptionTypes;
    }

    public function getNodeType(): string
    {
        return Catch_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Catch_) {
            return [];
        }

        $errors = [];

        foreach ($node->types as $type) {
            $exceptionType = $type->toString();

            // Check if the caught exception type is in the forbidden list
            if (in_array($exceptionType, $this->forbiddenExceptionTypes, true)) {
                $errors[] = RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $exceptionType))
                    ->line($node->getLine())
                    ->identifier(self::IDENTIFIER)
                    ->build();
            }
        }

        return $errors;
    }
}
