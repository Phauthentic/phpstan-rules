<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\CleanCode;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Specification:
 *
 * - Checks if a line exceeds the configured maximum line length.
 * - Optionally excludes files matching specific patterns.
 * - Optionally ignores use statements (BC: via ignoreUseStatements parameter or 'useStatements' in ignoreLineTypes).
 * - Optionally ignores namespace declaration (via 'namespaceDeclaration' in ignoreLineTypes).
 * - Optionally ignores docblock comments (via 'docBlocks' in ignoreLineTypes).
 *
 * @implements Rule<Node>
 */
class MaxLineLengthRule implements Rule
{
    private const ERROR_MESSAGE = 'Line %d exceeds the maximum length of %d characters (found %d characters).';

    private const IDENTIFIER = 'phauthentic.cleancode.maxLineLength';

    private int $maxLineLength;

    /**
     * @var string[]
     */
    private array $excludePatterns;

    private bool $ignoreUseStatements;

    private bool $ignoreNamespaceDeclaration;

    private bool $ignoreDocBlocks;

    /**
     * @var array<string, array<int, int>>
     */
    private array $fileLineLengths = [];

    /**
     * @var array<string, array<int, bool>>
     */
    private array $processedLines = [];

    /**
     * @var array<string, array<int, bool>>
     * Cache of which lines contain use statements per file
     */
    private array $useStatementLines = [];

    /**
     * @var array<string, array<int, bool>>
     * Cache of which lines contain namespace statements per file
     */
    private array $namespaceLines = [];

    /**
     * @var array<string, array<int, bool>>
     * Cache of which lines contain docblock comments per file
     */
    private array $docBlockLines = [];

    /**
     * @param string[] $excludePatterns
     * @param array<string, bool> $ignoreLineTypes Array of line types to ignore (e.g., ['useStatements' => true, 'namespaceDeclaration' => true, 'docBlocks' => true])
     */
    public function __construct(
        int $maxLineLength,
        array $excludePatterns = [],
        bool $ignoreUseStatements = false,
        array $ignoreLineTypes = []
    ) {
        $this->maxLineLength = $maxLineLength;
        $this->excludePatterns = $excludePatterns;

        // BC: ignoreUseStatements parameter takes precedence over array when both are set
        $this->ignoreUseStatements = $ignoreUseStatements ?: ($ignoreLineTypes['useStatements'] ?? false);
        $this->ignoreNamespaceDeclaration = $ignoreLineTypes['namespaceDeclaration'] ?? false;
        $this->ignoreDocBlocks = $ignoreLineTypes['docBlocks'] ?? false;
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * Processes the node and checks if its line exceeds the maximum length.
     *
     * @param Node $node The node to process.
     * @param Scope $scope The scope of the node.
     * @return RuleError[] An array of rule errors if any.
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Skip PHPStan-specific nodes that don't represent actual PHP code
        if ($node instanceof FileNode) {
            return [];
        }

        // Skip if file should be excluded
        if ($this->shouldExcludeFile($scope)) {
            return [];
        }

        $filePath = $scope->getFile();
        $lineNumber = $node->getStartLine();

        // Track use statement lines for this file
        if ($node instanceof Use_) {
            $this->markLineAsUseStatement($filePath, $lineNumber);

            // If ignoring use statements, skip processing this node
            if ($this->ignoreUseStatements) {
                return [];
            }
        }

        // Track namespace statement lines for this file
        if ($node instanceof Namespace_) {
            // Only mark the start line where the namespace declaration appears
            $namespaceLine = $node->getStartLine();
            $this->markLineAsNamespace($filePath, $namespaceLine);

            // If ignoring namespaces and this is the namespace declaration line, skip it
            if ($this->ignoreNamespaceDeclaration && $lineNumber === $namespaceLine) {
                return [];
            }
        }

        // Handle docblock lines for this node
        $errors = [];
        $docComment = $node->getDocComment();
        if ($docComment !== null) {
            $startLine = $docComment->getStartLine();
            $endLine = $docComment->getEndLine();

            // Mark all docblock lines
            for ($line = $startLine; $line <= $endLine; $line++) {
                $this->markLineAsDocBlock($filePath, $line);
            }

            // If not ignoring docblocks, check each line in the docblock
            if (!$this->ignoreDocBlocks) {
                for ($line = $startLine; $line <= $endLine; $line++) {
                    // Skip if we've already processed this line
                    if ($this->isLineProcessed($filePath, $line)) {
                        continue;
                    }

                    $lineLength = $this->getLineLength($filePath, $line);
                    if ($lineLength > $this->maxLineLength) {
                        $this->markLineAsProcessed($filePath, $line);
                        $errors[] = RuleErrorBuilder::message($this->buildErrorMessage($line, $lineLength))
                            ->identifier(self::IDENTIFIER)
                            ->line($line)
                            ->build();
                    }
                }
            }
        }

        // Skip if this line is a use statement and we're ignoring them
        if ($this->ignoreUseStatements && $this->isUseStatementLine($filePath, $lineNumber)) {
            return [];
        }

        // Skip if this line is a namespace and we're ignoring them
        if ($this->ignoreNamespaceDeclaration && $this->isNamespaceLine($filePath, $lineNumber)) {
            return [];
        }

        // Skip if this line is a docblock and we're ignoring them
        if ($this->ignoreDocBlocks && $this->isDocBlockLine($filePath, $lineNumber)) {
            return [];
        }

        // Skip if we've already processed this line
        if ($this->isLineProcessed($filePath, $lineNumber)) {
            return [];
        }

        // Get line length for this specific line
        $lineLength = $this->getLineLength($filePath, $lineNumber);

        if ($lineLength > $this->maxLineLength) {
            $this->markLineAsProcessed($filePath, $lineNumber);

            $errors[] = RuleErrorBuilder::message($this->buildErrorMessage($lineNumber, $lineLength))
                ->identifier(self::IDENTIFIER)
                ->line($lineNumber)
                ->build();
        }

        return $errors;
    }

    private function shouldExcludeFile(Scope $scope): bool
    {
        if (empty($this->excludePatterns)) {
            return false;
        }

        $filePath = $scope->getFile();

        foreach ($this->excludePatterns as $pattern) {
            if (preg_match($pattern, $filePath)) {
                return true;
            }
        }

        return false;
    }

    private function isLineProcessed(string $filePath, int $lineNumber): bool
    {
        return isset($this->processedLines[$filePath][$lineNumber]);
    }

    private function markLineAsProcessed(string $filePath, int $lineNumber): void
    {
        if (!isset($this->processedLines[$filePath])) {
            $this->processedLines[$filePath] = [];
        }

        $this->processedLines[$filePath][$lineNumber] = true;
    }

    private function isUseStatementLine(string $filePath, int $lineNumber): bool
    {
        return isset($this->useStatementLines[$filePath][$lineNumber]);
    }

    private function markLineAsUseStatement(string $filePath, int $lineNumber): void
    {
        if (!isset($this->useStatementLines[$filePath])) {
            $this->useStatementLines[$filePath] = [];
        }

        $this->useStatementLines[$filePath][$lineNumber] = true;
    }

    private function isNamespaceLine(string $filePath, int $lineNumber): bool
    {
        return isset($this->namespaceLines[$filePath][$lineNumber]);
    }

    private function markLineAsNamespace(string $filePath, int $lineNumber): void
    {
        if (!isset($this->namespaceLines[$filePath])) {
            $this->namespaceLines[$filePath] = [];
        }

        $this->namespaceLines[$filePath][$lineNumber] = true;
    }

    private function isDocBlockLine(string $filePath, int $lineNumber): bool
    {
        return isset($this->docBlockLines[$filePath][$lineNumber]);
    }

    private function markLineAsDocBlock(string $filePath, int $lineNumber): void
    {
        if (!isset($this->docBlockLines[$filePath])) {
            $this->docBlockLines[$filePath] = [];
        }

        $this->docBlockLines[$filePath][$lineNumber] = true;
    }

    private function getLineLength(string $filePath, int $lineNumber): int
    {
        // Cache file line lengths to avoid reading the same file multiple times
        if (!isset($this->fileLineLengths[$filePath])) {
            $this->fileLineLengths[$filePath] = $this->readFileLineLengths($filePath);
        }

        return $this->fileLineLengths[$filePath][$lineNumber] ?? 0;
    }

    /**
     * @return array<int, int>
     */
    private function readFileLineLengths(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $lineLengths = [];

        foreach ($lines as $index => $line) {
            // Line numbers are 1-indexed in PHP-Parser
            $lineNumber = $index + 1;
            // Remove carriage return if present and count actual characters
            $line = rtrim($line, "\r");
            $lineLengths[$lineNumber] = strlen($line);
        }

        return $lineLengths;
    }

    private function buildErrorMessage(int $lineNumber, int $lineLength): string
    {
        return sprintf(
            self::ERROR_MESSAGE,
            $lineNumber,
            $this->maxLineLength,
            $lineLength
        );
    }
}
