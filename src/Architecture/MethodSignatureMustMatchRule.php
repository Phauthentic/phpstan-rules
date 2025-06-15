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
 * - Checks if the classname plus method name matches a given regex pattern.
 * - Check the min and max number of parameters.
 * - Check if the types of the parameters match the expected types.
 */
class MethodSignatureMustMatchRule implements Rule
{
    /**
     * @var array<array{
     *     pattern: string,
     *     minParameters: null|int,
     *     maxParameters: null|int,
     *     signature: array<array{
     *         type: string,
     *         pattern: string|null,
     *     }>
     * }>
     */
    private array $signaturePatterns;

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
    public function __construct(array $signaturePatterns)
    {
        $this->signaturePatterns = $signaturePatterns;
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

                foreach ([
                    $this->checkMinParameters($patternConfig, $paramCount, $fullName, $method),
                    $this->checkMaxParameters($patternConfig, $paramCount, $fullName, $method)
                ] as $paramErrors) {
                    foreach ($paramErrors as $error) {
                        $errors[] = $error;
                    }
                }

                // Check parameter types and patterns
                if (!empty($patternConfig['signature'])) {
                    foreach ($patternConfig['signature'] as $i => $expected) {
                        if (!isset($method->params[$i])) {
                            $errors[] = RuleErrorBuilder::message(
                                sprintf(
                                    'Method %s is missing parameter #%d of type %s.',
                                    $fullName,
                                    $i + 1,
                                    $expected['type']
                                )
                            )->line($method->getLine())->build();
                            continue;
                        }
                        $param = $method->params[$i];
                        $paramType = $param->type ? $this->getTypeAsString($param->type) : null;

                        if ($paramType !== $expected['type']) {
                            $errors[] = RuleErrorBuilder::message(
                                sprintf(
                                    'Method %s parameter #%d should be of type %s, %s given.',
                                    $fullName,
                                    $i + 1,
                                    $expected['type'],
                                    $paramType ?? 'none'
                                )
                            )->line($param->getLine())->build();
                        }

                        if ($this->isInvalidParameterName($expected, $param)) {
                            $errors[] = RuleErrorBuilder::message(
                                sprintf(
                                    'Method %s parameter #%d name "%s" does not match pattern %s.',
                                    $fullName,
                                    $i + 1,
                                    $param->var->name,
                                    $expected['pattern']
                                )
                            )->line($param->getLine())->build();
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
    private function checkMinParameters(array $patternConfig, int $paramCount, string $fullName, ClassMethod $method): array
    {
        if (
            $patternConfig['minParameters'] !== null &&
            $paramCount < $patternConfig['minParameters']
        ) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Method %s has %d parameters, but at least %d required.',
                        $fullName,
                        $paramCount,
                        $patternConfig['minParameters']
                    )
                )->line($method->getLine())->build()
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
    private function checkMaxParameters(array $patternConfig, int $paramCount, string $fullName, ClassMethod $method): array
    {
        if (
            $patternConfig['maxParameters'] !== null &&
            $paramCount > $patternConfig['maxParameters']
        ) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Method %s has %d parameters, but at most %d allowed.',
                        $fullName,
                        $paramCount,
                        $patternConfig['maxParameters']
                    )
                )->line($method->getLine())->build()
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
            $type instanceof NullableType => ($inner = $this->getTypeAsString($type->type)) !== null ? '?' . $inner : null,
            default => null,
        };
    }
}