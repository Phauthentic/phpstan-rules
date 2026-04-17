# Forbidden Super Globals Rule

Forbids direct use of PHP [superglobals](https://www.php.net/manual/en/language.variables.superglobals.php): `$GLOBALS`, `$_SERVER`, `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`, `$_SESSION`, `$_REQUEST`, and `$_ENV`. Only simple variable reads are reported (dynamic variable names are ignored).

## Why

Superglobals couple code to the global HTTP or CLI environment, make testing harder, and hide dependencies. Prefer constructor injection, framework request objects, or explicit parameters so boundaries stay clear.

## Semantics of `patterns`

Patterns follow the same convention as other method-targeting rules in this package: each entry is a PCRE regex matched against **`Full\Class\Name::methodName`** (FQCN of the class, `::`, then the method name).

- **`patterns` omitted or empty (`[]`):** the rule is **global**. Any use of a superglobal in **any** analyzed scope is reported (class methods, namespaced functions, file-level code, and so on).

- **`patterns` non-empty:** the rule runs **only inside class methods** where both the class and the enclosing function are known. The current method must match **at least one** pattern. Code outside a class method (for example a namespaced function) is **not** checked, because there is no `Fqcn::methodName` string in that form.

This differs from the [Forbidden Else Statements Rule](Forbidden-Else-Statements-Rule.md): there, an empty `patterns` list **disables** the rule. Here, an empty list means **no scope filter** (apply everywhere), matching the [Forbidden Date Time Comparison Rule](Forbidden-Date-Time-Comparison-Rule.md).

## Configuration example

Global (entire codebase):

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\ForbiddenSuperGlobalsRule
        arguments:
            patterns: []
        tags:
            - phpstan.rules.rule
```

Scoped to specific methods (regex on `Fqcn::methodName`):

```neon
    -
        class: Phauthentic\PHPStanRules\CleanCode\ForbiddenSuperGlobalsRule
        arguments:
            patterns:
                - '/^App\\\\Presentation\\\\Http\\\\.*Controller::(handle|__invoke)$/'
                - '/^App\\\\Module\\\\.*::execute$/'
        tags:
            - phpstan.rules.rule
```

## Parameters

- `patterns`: List of PCRE regex strings. When empty, the rule applies globally. When non-empty, each pattern is matched against `Full\Class\Name::methodName` for the enclosing class method; superglobals are only reported if any pattern matches.
