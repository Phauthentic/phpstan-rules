<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Architecture;

/**
 * Specification:
 *
 * - Checks use statements in PHP code.
 * - A class in a namespace matching a given regex is not allowed to depend on any namespace defined by a set of other regexes.
 * - Reports an error if a forbidden dependency is detected.
 * - Optionally checks fully qualified class names (FQCNs) in various contexts.
 *
 * @deprecated Use ForbiddenDependenciesRule instead. This class is kept for backward compatibility.
 *
 * @see ForbiddenDependenciesRule
 */
class DependencyConstraintsRule extends ForbiddenDependenciesRule
{
    protected const ERROR_MESSAGE = 'Dependency violation: A class in namespace `%s` is not allowed to depend on `%s`.';

    protected const IDENTIFIER = 'phauthentic.architecture.dependencyConstraints';
}
