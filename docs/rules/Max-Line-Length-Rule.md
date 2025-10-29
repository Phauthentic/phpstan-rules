# Max Line Length Rule

Checks that lines do not exceed a specified maximum length. Provides options to exclude files by pattern and ignore use statements.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\MaxLineLengthRule
        arguments:
            maxLineLength: 80
            excludePatterns: ['/.*\.generated\.php$/', '/.*vendor.*/']
            ignoreUseStatements: false
        tags:
            - phpstan.rules.rule
```

## Parameters

- `maxLineLength`: Maximum allowed line length in characters (default: 80).
- `excludePatterns`: Array of regex patterns to exclude files from checking (optional).
- `ignoreUseStatements`: Whether to ignore use statement lines (default: false).

