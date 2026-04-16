# Forbidden Date Time Comparison Rule

Forbids strict identity comparisons (`===` and `!==`) when **both** operands are known to implement `DateTimeInterface`.

## Why

In PHP, `===` and `!==` on objects compare **identity** (same object instance in memory), not whether two values represent the **same point in time**. Two distinct `DateTimeImmutable` instances with the same instant still compare as not identical with `===`. That makes `===` / `!==` a frequent source of subtle bugs when the intent was to compare calendar instants.

Loose equality (`==` / `!=`) for `DateTimeInterface` compares the dates for equality in the sense most callers expect. Alternatively, compare normalized instants explicitly (for example `getTimestamp()`, a canonical `format()`, or `DateTimeImmutable::createFromInterface()` followed by a defined comparison strategy).

## Semantics of `patterns`

Patterns use the same convention as other method-targeting rules in this package: each entry is a PCRE regex matched against **`Full\Class\Name::methodName`** (FQCN of the class, `::`, then the method name).

- **`patterns` omitted or empty (`[]`):** the rule is **global**. Any `===` / `!==` between two definitely-`DateTimeInterface` operands is reported in **any** analyzed scope (class methods, namespaced functions, file-level code, and so on), wherever PHPStan can prove both types.

- **`patterns` non-empty:** the rule runs **only inside class methods** where both the class and the enclosing function are known. The current method must match **at least one** pattern. Code outside a class method (for example a namespaced function) is **not** checked, because there is no `Fqcn::methodName` string in that form.

This differs from the [Forbidden Else Statements Rule](Forbidden-Else-Statements-Rule.md): there, an empty `patterns` list **disables** the rule. Here, an empty list means **no scope filter** (apply everywhere).

## Configuration example

Global (entire codebase):

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenDateTimeComparisonRule
        arguments:
            patterns: []
        tags:
            - phpstan.rules.rule
```

Scoped to specific methods (regex on `Fqcn::methodName`):

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\ForbiddenDateTimeComparisonRule
        arguments:
            patterns:
                - '/^App\\\\Presentation\\\\Http\\\\.*Controller::(handle|__invoke)$/'
                - '/^App\\\\Module\\\\.*::execute$/'
        tags:
            - phpstan.rules.rule
```

## Parameters

- `patterns`: List of PCRE regex strings. When empty, the rule applies globally. When non-empty, each pattern is matched against `Full\Class\Name::methodName` for the enclosing class method; the comparison is only checked if any pattern matches.
