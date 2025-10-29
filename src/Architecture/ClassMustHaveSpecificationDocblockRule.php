<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Specification:
 *
 * - Enforces that matched classes have a docblock with a "Specification:" section.
 * - The specification section must contain a list of items starting with "-".
 * - Optionally allows @ annotations after a blank line.
 * - Optionally allows additional text between the list and annotations.
 *
 * @implements Rule<Class_>
 */
class ClassMustHaveSpecificationDocblockRule implements Rule
{
    private const ERROR_MESSAGE_MISSING = 'Class %s must have a docblock with a "%s" section.';
    private const IDENTIFIER = 'phauthentic.architecture.classMustHaveSpecificationDocblock';

    /**
     * @param array<string> $patterns An array of regex patterns to match against class names.
     * Each pattern should be a valid PCRE regex.
     * @param string $specificationHeader The header text to look for (default: "Specification:")
     * @param bool $requireBlankLineAfterHeader Whether to require a blank line after the header (default: true)
     * @param bool $requireListItemsEndWithPeriod Whether list items must end with a period (default: false)
     */
    public function __construct(
        protected array $patterns,
        protected string $specificationHeader = 'Specification:',
        protected bool $requireBlankLineAfterHeader = true,
        protected bool $requireListItemsEndWithPeriod = false
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    private function buildInvalidFormatMessage(): string
    {
        $parts = ["Expected format: \"{$this->specificationHeader}\" header"];
        
        if ($this->requireBlankLineAfterHeader) {
            $parts[] = "blank line";
        }
        
        $parts[] = "then list items starting with \"-\"";
        
        $message = implode(', ', $parts) . '.';
        
        if ($this->requireListItemsEndWithPeriod) {
            $message .= ' List items must end with a period.';
        }
        
        return $message;
    }

    /**
     * @param Class_ $node
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!isset($node->name)) {
            return [];
        }

        $className = $node->name->toString();
        $namespaceName = $scope->getNamespace() ?? '';
        $fullClassName = $namespaceName . '\\' . $className;

        if (!$this->classNameMatchesPattern($fullClassName)) {
            return [];
        }

        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [$this->buildMissingDocblockError($fullClassName, $node)];
        }

        if (!$this->isValidSpecificationDocblock($docComment)) {
            return [$this->buildInvalidDocblockError($fullClassName, $node)];
        }

        return [];
    }

    private function classNameMatchesPattern(string $fullClassName): bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $fullClassName)) {
                return true;
            }
        }

        return false;
    }

    private function isValidSpecificationDocblock(Doc $docComment): bool
    {
        $text = $docComment->getText();
        $lines = $this->extractDocblockLines($text);

        if (!$this->hasSpecificationHeader($lines)) {
            return false;
        }

        if (!$this->hasValidSpecificationFormat($lines)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $text
     * @return array<int, string>
     */
    private function extractDocblockLines(string $text): array
    {
        // Remove /** and */
        $cleaned = preg_replace('/^\/\*\*|\*\/$/', '', $text);
        if ($cleaned === null) {
            return [];
        }
        
        // Split by lines
        $lines = explode("\n", $cleaned);
        
        // Remove leading * and whitespace from each line
        $lines = array_map(function (string $line): string {
            $line = ltrim($line);
            if (strpos($line, '*') === 0) {
                $line = substr($line, 1);
                // Only remove ONE space after the asterisk if it exists
                if (strpos($line, ' ') === 0) {
                    $line = substr($line, 1);
                }
            }
            return $line;
        }, $lines);

        // Remove empty first and last lines that might be from the /** */ delimiters
        if ($lines !== [] && trim($lines[0]) === '') {
            array_shift($lines);
        }
        $lastIndex = count($lines) - 1;
        if ($lastIndex >= 0 && trim($lines[$lastIndex]) === '') {
            array_pop($lines);
        }

        return $lines;
    }

    /**
     * @param array<int, string> $lines
     */
    private function hasSpecificationHeader(array $lines): bool
    {
        foreach ($lines as $line) {
            if (trim($line) === $this->specificationHeader) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $lines
     */
    private function hasValidSpecificationFormat(array $lines): bool
    {
        // Find the specification header line
        $specIndex = null;
        foreach ($lines as $index => $line) {
            if (trim($line) === $this->specificationHeader) {
                $specIndex = (int) $index;
                break;
            }
        }

        if ($specIndex === null) {
            return false;
        }

        // Next line after the specification header should be blank (if required)
        if ($this->requireBlankLineAfterHeader) {
            if (!isset($lines[$specIndex + 1]) || trim($lines[$specIndex + 1]) !== '') {
                return false;
            }
        }

        // Find at least one list item (starting with -)
        $hasListItem = false;
        $lineCount = count($lines);
        $startIndex = $this->requireBlankLineAfterHeader ? $specIndex + 2 : $specIndex + 1;
        
        $currentListItem = '';
        $inListItem = false;
        
        for ($i = $startIndex; $i < $lineCount; $i++) {
            if (!isset($lines[$i])) {
                break;
            }
            
            $trimmedLine = trim($lines[$i]);
            
            // Skip blank lines
            if ($trimmedLine === '') {
                // If we were in a list item, finalize it
                if ($inListItem && $currentListItem !== '') {
                    if (!$this->validateListItem($currentListItem)) {
                        return false;
                    }
                    $currentListItem = '';
                    $inListItem = false;
                }
                continue;
            }
            
            // If we hit an @ annotation, finalize current list item and stop
            if (strpos($trimmedLine, '@') === 0) {
                if ($inListItem && $currentListItem !== '') {
                    if (!$this->validateListItem($currentListItem)) {
                        return false;
                    }
                }
                break;
            }
            
            // Check if this is a new list item (starts with -)
            if (strpos($trimmedLine, '-') === 0) {
                // Finalize previous list item if exists
                if ($inListItem && $currentListItem !== '') {
                    if (!$this->validateListItem($currentListItem)) {
                        return false;
                    }
                }
                
                // Start new list item
                $hasListItem = true;
                $inListItem = true;
                $currentListItem = $trimmedLine;
                continue;
            }
            
            // If we're in a list item, this is a continuation line
            if ($inListItem) {
                $currentListItem .= ' ' . $trimmedLine;
                continue;
            }
            
            // If we encounter non-list, non-annotation, non-blank line before finding a list item, invalid
            if (!$hasListItem) {
                return false;
            }
        }
        
        // Finalize the last list item if exists
        if ($inListItem && $currentListItem !== '') {
            if (!$this->validateListItem($currentListItem)) {
                return false;
            }
        }

        return $hasListItem;
    }

    /**
     * Validate a complete list item (may span multiple lines)
     */
    private function validateListItem(string $listItem): bool
    {
        if ($this->requireListItemsEndWithPeriod) {
            return $this->listItemEndsWithPeriod($listItem);
        }
        
        return true;
    }

    /**
     * Check if a list item ends with a period
     */
    private function listItemEndsWithPeriod(string $listItem): bool
    {
        $trimmed = trim($listItem);
        return str_ends_with($trimmed, '.');
    }

    /**
     * @throws ShouldNotHappenException
     * @return \PHPStan\Rules\IdentifierRuleError
     */
    private function buildMissingDocblockError(string $fullClassName, Class_ $node)
    {
        return RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE_MISSING, $fullClassName, $this->specificationHeader))
            ->identifier(self::IDENTIFIER)
            ->line($node->getLine())
            ->build();
    }

    /**
     * @throws ShouldNotHappenException
     * @return \PHPStan\Rules\IdentifierRuleError
     */
    private function buildInvalidDocblockError(string $fullClassName, Class_ $node)
    {
        return RuleErrorBuilder::message(sprintf('Class %s has an invalid specification docblock. %s', $fullClassName, $this->buildInvalidFormatMessage()))
            ->identifier(self::IDENTIFIER)
            ->line($node->getLine())
            ->build();
    }
}

