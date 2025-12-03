<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Specification:
 *
 * - Checks use statements in PHP code.
 * - A class in a namespace matching a given regex is not allowed to depend on any namespace defined by a set of other regexes.
 * - Reports an error if a forbidden dependency is detected.
 * - Optionally checks fully qualified class names (FQCNs) in various contexts.
 *
 * @implements Rule<Node>
 */
class DependencyConstraintsRule implements Rule
{
    private const ERROR_MESSAGE = 'Dependency violation: A class in namespace `%s` is not allowed to depend on `%s`.';

    private const IDENTIFIER = 'phauthentic.architecture.dependencyConstraints';

    private const ALL_REFERENCE_TYPES = [
        'new',
        'param',
        'return',
        'property',
        'static_call',
        'static_property',
        'class_const',
        'instanceof',
        'catch',
        'extends',
        'implements',
    ];

    /**
     * @var array<string, array<string>>
     * An array where the key is a regex for the source namespace and the value is
     * an array of regexes for disallowed dependency namespaces.
     * e.g., ['#^App\\Domain\\.*#' => ['#^App\\Infrastructure\\.*#']]
     */
    private array $forbiddenDependencies;

    /**
     * @var bool
     */
    private bool $checkFqcn;

    /**
     * @var array<string>
     */
    private array $fqcnReferenceTypes;

    /**
     * @param array<string, array<string>> $forbiddenDependencies
     * @param bool $checkFqcn Enable checking of fully qualified class names (default: false for backward compatibility)
     * @param array<string> $fqcnReferenceTypes Which reference types to check when checkFqcn is enabled (default: all)
     */
    public function __construct(
        array $forbiddenDependencies,
        bool $checkFqcn = false,
        array $fqcnReferenceTypes = self::ALL_REFERENCE_TYPES
    ) {
        $this->forbiddenDependencies = $forbiddenDependencies;
        $this->checkFqcn = $checkFqcn;
        $this->fqcnReferenceTypes = $fqcnReferenceTypes;
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @return array<RuleError>
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $currentNamespace = $scope->getNamespace();
        if ($currentNamespace === null) {
            return [];
        }

        $errors = [];

        // Process use statements (original behavior - always active)
        if ($node instanceof Use_) {
            foreach ($this->forbiddenDependencies as $sourceNamespacePattern => $disallowedDependencyPatterns) {
                if (!preg_match($sourceNamespacePattern, $currentNamespace)) {
                    continue;
                }

                $errors = $this->validateUseStatements($node, $disallowedDependencyPatterns, $currentNamespace, $errors);
            }
        }

        // Process FQCN references (new behavior - optional)
        if ($this->checkFqcn) {
            $errors = array_merge($errors, $this->processFqcnNode($node, $currentNamespace));
        }

        return $errors;
    }

    /**
     * @param Use_ $node
     * @param array<string> $disallowedDependencyPatterns
     * @param string $currentNamespace
     * @param array<RuleError> $errors
     * @return array<RuleError>
     * @throws ShouldNotHappenException
     */
    public function validateUseStatements(Use_ $node, array $disallowedDependencyPatterns, string $currentNamespace, array $errors): array
    {
        foreach ($node->uses as $use) {
            $usedClassName = $use->name->toString();
            foreach ($disallowedDependencyPatterns as $disallowedPattern) {
                if (preg_match($disallowedPattern, $usedClassName)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        self::ERROR_MESSAGE,
                        $currentNamespace,
                        $usedClassName
                    ))
                    ->identifier(self::IDENTIFIER)
                    ->line($use->getStartLine())
                    ->build();
                }
            }
        }

        return $errors;
    }

    /**
     * Process FQCN references in various node types
     *
     * @param Node $node
     * @param string $currentNamespace
     * @return array<RuleError>
     */
    private function processFqcnNode(Node $node, string $currentNamespace): array
    {
        $classNames = $this->extractClassNamesFromFqcnNode($node);

        $errors = [];
        foreach ($classNames as $className) {
            $errors = array_merge($errors, $this->validateClassReference($className, $currentNamespace, $node));
        }

        return $errors;
    }

    /**
     * Extract class names from FQCN nodes based on node type
     *
     * @param Node $node
     * @return array<string>
     */
    private function extractClassNamesFromFqcnNode(Node $node): array
    {
        $classNames = [];

        if ($this->isExpressionNode($node)) {
            $classNames = $this->extractFromExpressionNode($node);
        } elseif ($this->isStatementNode($node)) {
            $classNames = $this->extractFromStatementNode($node);
        }

        return $classNames;
    }

    /**
     * Check if node is an expression node we want to process
     *
     * @param Node $node
     * @return bool
     */
    private function isExpressionNode(Node $node): bool
    {
        return $node instanceof Node\Expr\New_
            || $node instanceof Node\Expr\StaticCall
            || $node instanceof Node\Expr\StaticPropertyFetch
            || $node instanceof Node\Expr\ClassConstFetch
            || $node instanceof Node\Expr\Instanceof_;
    }

    /**
     * Check if node is a statement node we want to process
     *
     * @param Node $node
     * @return bool
     */
    private function isStatementNode(Node $node): bool
    {
        return $node instanceof Node\Stmt\Catch_
            || $node instanceof Node\Stmt\Class_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Stmt\Property;
    }

    /**
     * Extract class names from expression nodes
     *
     * @param Node $node
     * @return array<string>
     */
    private function extractFromExpressionNode(Node $node): array
    {
        $mapping = [
            Node\Expr\New_::class => 'new',
            Node\Expr\StaticCall::class => 'static_call',
            Node\Expr\StaticPropertyFetch::class => 'static_property',
            Node\Expr\ClassConstFetch::class => 'class_const',
            Node\Expr\Instanceof_::class => 'instanceof',
        ];

        foreach ($mapping as $nodeClass => $referenceType) {
            if ($node instanceof $nodeClass && $this->shouldCheckReferenceType($referenceType)) {
                return $this->extractClassNamesFromNode($node->class);
            }
        }

        return [];
    }

    /**
     * Extract class names from statement nodes
     *
     * @param Node $node
     * @return array<string>
     */
    private function extractFromStatementNode(Node $node): array
    {
        $classNames = [];

        if ($node instanceof Node\Stmt\Catch_ && $this->shouldCheckReferenceType('catch')) {
            $classNames = $this->extractFromCatchNode($node);
        } elseif ($node instanceof Node\Stmt\Class_) {
            $classNames = $this->extractFromClassNode($node);
        } elseif ($node instanceof Node\Stmt\ClassMethod) {
            $classNames = $this->extractFromClassMethodNode($node);
        } elseif ($node instanceof Node\Stmt\Property && $this->shouldCheckReferenceType('property')) {
            if ($node->type !== null) {
                $classNames = $this->extractClassNamesFromType($node->type);
            }
        }

        return $classNames;
    }

    /**
     * Extract class names from catch nodes
     *
     * @param Node\Stmt\Catch_ $node
     * @return array<string>
     */
    private function extractFromCatchNode(Node\Stmt\Catch_ $node): array
    {
        $classNames = [];
        foreach ($node->types as $type) {
            $classNames = array_merge($classNames, $this->extractClassNamesFromNode($type));
        }
        return $classNames;
    }

    /**
     * Extract class names from class nodes (extends/implements)
     *
     * @param Node\Stmt\Class_ $node
     * @return array<string>
     */
    private function extractFromClassNode(Node\Stmt\Class_ $node): array
    {
        $classNames = [];

        if ($node->extends !== null && $this->shouldCheckReferenceType('extends')) {
            $classNames = array_merge($classNames, $this->extractClassNamesFromNode($node->extends));
        }

        if ($this->shouldCheckReferenceType('implements')) {
            foreach ($node->implements as $interface) {
                $classNames = array_merge($classNames, $this->extractClassNamesFromNode($interface));
            }
        }

        return $classNames;
    }

    /**
     * Extract class names from class method nodes (parameters and return types)
     *
     * @param Node\Stmt\ClassMethod $node
     * @return array<string>
     */
    private function extractFromClassMethodNode(Node\Stmt\ClassMethod $node): array
    {
        $classNames = [];

        if ($this->shouldCheckReferenceType('param')) {
            foreach ($node->params as $param) {
                if ($param->type !== null) {
                    $classNames = array_merge($classNames, $this->extractClassNamesFromType($param->type));
                }
            }
        }

        if ($this->shouldCheckReferenceType('return') && $node->returnType !== null) {
            $classNames = array_merge($classNames, $this->extractClassNamesFromType($node->returnType));
        }

        return $classNames;
    }

    /**
     * Check if a reference type should be validated
     *
     * @param string $referenceType
     * @return bool
     */
    private function shouldCheckReferenceType(string $referenceType): bool
    {
        return in_array($referenceType, $this->fqcnReferenceTypes, true);
    }

    /**
     * Extract class names from a node (handles Name nodes)
     *
     * @param Node|Identifier|Name|ComplexType $node
     * @return array<string>
     */
    private function extractClassNamesFromNode($node): array
    {
        if ($node instanceof Name && $this->isFullyQualifiedName($node)) {
            return [$node->toString()];
        }
        return [];
    }

    /**
     * Extract class names from type declarations (handles complex types)
     *
     * @param Identifier|Name|ComplexType $type
     * @return array<string>
     */
    private function extractClassNamesFromType($type): array
    {
        $classNames = [];

        if ($type instanceof Name) {
            if ($this->isFullyQualifiedName($type)) {
                $classNames[] = $type->toString();
            }
        } elseif ($type instanceof NullableType) {
            $classNames = array_merge($classNames, $this->extractClassNamesFromType($type->type));
        } elseif ($type instanceof UnionType || $type instanceof IntersectionType) {
            foreach ($type->types as $subType) {
                $classNames = array_merge($classNames, $this->extractClassNamesFromType($subType));
            }
        }

        return $classNames;
    }

    /**
     * Check if a Name node represents a fully qualified class name
     *
     * @param Name $name
     * @return bool
     */
    private function isFullyQualifiedName(Name $name): bool
    {
        return $name instanceof Name\FullyQualified;
    }

    /**
     * Validate a class reference against forbidden dependencies
     *
     * @param string $className
     * @param string $currentNamespace
     * @param Node $node
     * @return array<RuleError>
     */
    private function validateClassReference(string $className, string $currentNamespace, Node $node): array
    {
        $errors = [];

        foreach ($this->forbiddenDependencies as $sourceNamespacePattern => $disallowedDependencyPatterns) {
            if (!preg_match($sourceNamespacePattern, $currentNamespace)) {
                continue;
            }

            foreach ($disallowedDependencyPatterns as $disallowedPattern) {
                if (preg_match($disallowedPattern, $className)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        self::ERROR_MESSAGE,
                        $currentNamespace,
                        $className
                    ))
                    ->identifier(self::IDENTIFIER)
                    ->line($node->getLine())
                    ->build();
                }
            }
        }

        return $errors;
    }
}
