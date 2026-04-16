# Forbidden Business Logic Rule

Forbids selected imperative control structures inside class methods: `if`, `for`, `foreach`, `while`, and `switch`. Typical use cases include pushing branching and iteration behind domain objects or policy objects in specific layers.

The rule evaluates the current scope as `Full\Class\Name::methodName` (from PHPStan’s class and function reflections). Anonymous functions and global functions are out of scope when the class or function reflection is missing.

## Global list and per-pattern overrides

- **`forbiddenStatements`**: Default set of construct names to forbid in every class method. Names are case-insensitive. Unknown names are ignored. If this list is empty, nothing is forbidden unless a matching pattern entry supplies a non-empty `forbiddenStatements` list.
- **`patterns`**: Ordered list of entries. Each entry has a **`pattern`** (regex matched against `Fqcn::methodName`). Optionally **`forbiddenStatements`**: if present on an entry whose pattern matches, it **replaces** the effective forbidden list entirely for that method. If several entries match, the **last** matching entry that defines `forbiddenStatements` wins. Entries that match but omit `forbiddenStatements` do not change the effective list (it stays as set by global and earlier matches).

The default constructor uses all five constructs globally and an empty `patterns` list, which forbids those constructs everywhere in class methods.

## Legacy `patterns` format

You may pass plain regex strings; each is normalised to `{ pattern: "..." }` without an override, so only the global list applies for methods that do not receive a later matching override with `forbiddenStatements`.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenBusinessLogicRule
        arguments:
            forbiddenStatements:
                - if
                - for
                - foreach
                - while
                - switch
            patterns:
                -
                    pattern: '/^App\\\\Domain\\\\.*::/'
                    forbiddenStatements:
                        - if
                        - switch
                -
                    pattern: '/^App\\\\Application\\\\.*Workflow::run$/'
                    forbiddenStatements:
                        - foreach
        tags:
            - phpstan.rules.rule
```

## Parameters

- `forbiddenStatements` (`list<string>`): Global list of forbidden construct names: `if`, `for`, `foreach`, `while`, `switch`.
- `patterns` (`list<array{pattern: string, forbiddenStatements?: list<string>}> | list<string>`): Regex entries against `Fqcn::methodName`, optionally with per-entry `forbiddenStatements` that replace the effective list when that pattern matches (last such match wins).

## Class-wide patterns

Match every method on a class with a regex on the `::` prefix, for example `/^App\\\\Module\\\\Foo::/` or `/^App\\\\Module\\\\Foo::.+$/`.

## Ignoring violations

Use PHPStan baseline or inline `@phpstan-ignore` with a short justification when a rare exception is required.
