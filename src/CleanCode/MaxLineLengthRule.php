<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\CleanCode;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * A configurable rule to check the maximum line length.
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

    /**
     * @var array<string, array<int, int>>
     */
    private array $fileLineLengths = [];

    /**
     * @var array<string, array<int, bool>>
     */
    private array $processedLines = [];

    /**
     * @param string[] $excludePatterns
     */
    public function __construct(int $maxLineLength, array $excludePatterns = [], bool $ignoreUseStatements = false)
    {
        $this->maxLineLength = $maxLineLength;
        $this->excludePatterns = $excludePatterns;
        $this->ignoreUseStatements = $ignoreUseStatements;
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
        // Skip if file should be excluded
        if ($this->shouldExcludeFile($scope)) {
            return [];
        }

        // Skip use statements if configured to ignore them
        if ($this->ignoreUseStatements && $node instanceof Use_) {
            return [];
        }

        $filePath = $scope->getFile();
        $lineNumber = $node->getLine();

        // Skip if we've already processed this line
        if ($this->isLineProcessed($filePath, $lineNumber)) {
            return [];
        }

        // Get line length for this specific line
        $lineLength = $this->getLineLength($filePath, $lineNumber);

        if ($lineLength > $this->maxLineLength) {
            $this->markLineAsProcessed($filePath, $lineNumber);

            return [
                RuleErrorBuilder::message($this->buildErrorMessage($lineNumber, $lineLength))
                    ->identifier(self::IDENTIFIER)
                    ->line($lineNumber)
                    ->build()
            ];
        }

        return [];
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
