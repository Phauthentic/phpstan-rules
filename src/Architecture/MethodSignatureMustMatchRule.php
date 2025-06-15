<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Specification:
 * - Checks if the classname plus method name matches a given regex pattern.
 * - Check the min and max number of parameters.
 * - Check if the types of the parameters match the expected types.
 */
class MethodSignatureMustMatchRule implements Rule
{
    private const IDENTIFIER = 'phauthentic.architecture.methodSignatureMustMatch';

    private const ERROR_MESSAGE_MISSING_PARAMETER = 'Method %s is missing parameter #%d of type %s.';
    private const ERROR_MESSAGE_WRONG_TYPE = 'Method %s parameter #%d should be of type %s, %s given.';
    private const ERROR_MESSAGE_NAME_PATTERN = 'Method %s parameter #%d name "%s" does not match pattern %s.';
    private const ERROR_MESSAGE_MIN_PARAMETERS = 'Method %s has %d parameters, but at least %d required.';
    private const ERROR_MESSAGE_MAX_PARAMETERS = 'Method %s has %d parameters, but at most %d allowed.';

    /**
     * @param array<array{
     *     pattern: string,
     *     minParameters: null|int,
     *     maxParameters: null|int,
     *     signature: array<array{
     *         type: string,
     *         pattern: string|null,
     *     }>
     * }> $signaturePatterns
     */
    public function __construct(
        protected array $signaturePatterns
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @param Scope $scope
     * @return array
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $className = $node->name ? $node->name->toString() : '';

        foreach ($node->getMethods() as $method) {
            $methodName = $method->name->toString();
            $fullName = $className . '::' . $methodName;

            foreach ($this->signaturePatterns as $patternConfig) {
                if (!preg_match($patternConfig['pattern'], $fullName)) {
                    continue;
                }

                $paramCount = count($method->params);

                $minParamErrors = $this->checkMinParameters(
                    patternConfig: $patternConfig,
                    paramCount: $paramCount,
                    fullName: $fullName,
                    method: $method
                );

                $maxParamErrors = $this->checkMaxParameters(
                    patternConfig: $patternConfig,
                    paramCount: $paramCount,
                    fullName: $fullName,
                    method: $method
                );

                foreach ([$minParamErrors, $maxParamErrors] as $paramErrors) {
                    foreach ($paramErrors as $error) {
                        $errors[] = $error;
                    }
                }

                // Check parameter types and patterns
                if (!empty($patternConfig['signature'])) {
                    foreach ($patternConfig['signature'] as $i => $expected) {
                        if (!isset($method->params[$i])) {
                            $errors[] = RuleErrorBuilder::message(
                                message: sprintf(
                                    self::ERROR_MESSAGE_MISSING_PARAMETER,
                                    $fullName,
                                    $i + 1,
                                    $expected['type']
                                )
                            )
                            ->identifier(self::IDENTIFIER)
                            ->line($method->getLine())
                            ->build();

                            continue;
                        }

                        $param = $method->params[$i];
                        $paramType = $param->type ? $this->getTypeAsString($param->type) : null;

                        if ($paramType !== $expected['type']) {
                            $errors[] = RuleErrorBuilder::message(
                                message: sprintf(
                                    self::ERROR_MESSAGE_WRONG_TYPE,
                                    $fullName,
                                    $i + 1,
                                    $expected['type'],
                                    $paramType ?? 'none'
                                )
                            )
                            ->identifier(identifier: self::IDENTIFIER)
                            ->line(line: $param->getLine())
                            ->build();
                        }

                        if ($this->isInvalidParameterName(expected: $expected, param: $param)) {
                            $errors[] = RuleErrorBuilder::message(
                                message: sprintf(
                                    self::ERROR_MESSAGE_NAME_PATTERN,
                                    $fullName,
                                    $i + 1,
                                    $param->var->name,
                                    $expected['pattern']
                                )
                            )
                            ->identifier(self::IDENTIFIER)
                            ->line($param->getLine())
                            ->build();
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param array $patternConfig
     * @param int $paramCount
     * @param string $fullName
     * @param ClassMethod $method
     * @return array
     */
    private function checkMinParameters(
        array $patternConfig,
        int $paramCount,
        string $fullName,
        ClassMethod $method
    ): array {
        if (
            $patternConfig['minParameters'] !== null &&
            $paramCount < $patternConfig['minParameters']
        ) {
            return [
                RuleErrorBuilder::message(
                    message: sprintf(
                        self::ERROR_MESSAGE_MIN_PARAMETERS,
                        $fullName,
                        $paramCount,
                        $patternConfig['minParameters']
                    )
                )
                ->identifier(self::IDENTIFIER)
                ->line($method->getLine())
                ->build()
            ];
        }

        return [];
    }

    /**
     * @param array $patternConfig
     * @param int $paramCount
     * @param string $fullName
     * @param ClassMethod $method
     * @return array
     */
    private function checkMaxParameters(
        array $patternConfig,
        int $paramCount,
        string $fullName,
        ClassMethod $method
    ): array {
        if (
            $patternConfig['maxParameters'] !== null &&
            $paramCount > $patternConfig['maxParameters']
        ) {
            return [
                RuleErrorBuilder::message(
                    message: sprintf(
                        self::ERROR_MESSAGE_MAX_PARAMETERS,
                        $fullName,
                        $paramCount,
                        $patternConfig['maxParameters']
                    )
                )
                ->identifier(self::IDENTIFIER)
                ->line($method->getLine())
                ->build()
            ];
        }
        return [];
    }

    /**
     * Checks if the parameter name does not match the expected pattern.
     *
     * @param array $expected
     * @param \PhpParser\Node\Param $param
     * @return bool
     */
    private function isInvalidParameterName(array $expected, $param): bool
    {
        return isset($expected['pattern'])
            && $expected['pattern'] !== null
            && $param->var->name !== null
            && !preg_match($expected['pattern'], (string)$param->var->name);
    }

    private function getTypeAsString(mixed $type): ?string
    {
        return match (true) {
            $type === null => null,
            $type instanceof Identifier => $type->name,
            $type instanceof Name => $type->toString(),
            $type instanceof NullableType =>
                ($inner = $this->getTypeAsString($type->type)) !== null ? '?' . $inner : null,
            default => null,
        };
    }
}
