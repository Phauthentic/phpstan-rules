# Max Line Length Rule

Checks that lines do not exceed a specified maximum length. Provides options to exclude files by pattern and ignore specific types of lines (use statements, namespace declarations, docblocks).

## Configuration Examples

### Using the new array API (recommended)

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule
        arguments:
            maxLineLength: 80
            excludePatterns: ['/.*\.generated\.php$/', '/.*vendor.*/']
            ignoreUseStatements: false
            ignoreLineTypes:
                useStatements: true
                namespaceDeclaration: true
                docBlocks: true
        tags:
            - phpstan.rules.rule
```

### Using the legacy parameter (backward compatible)

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule
        arguments:
            maxLineLength: 80
            excludePatterns: []
            ignoreUseStatements: true
        tags:
            - phpstan.rules.rule
```

## Parameters

- `maxLineLength`: Maximum allowed line length in characters (required).
- `excludePatterns`: Array of regex patterns to exclude files from checking (optional, default: `[]`).
- `ignoreUseStatements`: Whether to ignore use statement lines (optional, default: `false`). **Note:** This parameter is maintained for backward compatibility. When set to `true`, it takes precedence over the `ignoreLineTypes` array.
- `ignoreLineTypes`: Array of line types to ignore when checking line length (optional, default: `[]`). Available options:
  - `useStatements`: Ignore lines containing `use` statements
  - `namespaceDeclaration`: Ignore lines containing `namespace` declarations
  - `docBlocks`: Ignore lines that are part of docblock comments (`/** ... */`)

## Examples

### Ignore only use statements

```neon
ignoreLineTypes:
    useStatements: true
```

### Ignore namespace declarations and docblocks

```neon
ignoreLineTypes:
    namespaceDeclaration: true
    docBlocks: true
```

### Ignore all supported line types

```neon
ignoreLineTypes:
    useStatements: true
    namespaceDeclaration: true
    docBlocks: true
```
