# DocBlock Must Be Inherited Rule

Enforces that methods matching a class and method name pattern must have a docblock containing `@inheritDoc` or `@inheritdoc`. This rule is useful when you want to ensure that certain methods properly document their inheritance from parent classes or interfaces.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\DocBlockMustBeInheritedRule
        arguments:
            patterns:
                - '/^App\\Controller\\.*Controller::.*Action$/'
                - '/^App\\Repository\\.*Repository::find/'
                - '/^App\\Service\\.*Service::execute$/'
        tags:
            - phpstan.rules.rule
```

## Parameters

- `patterns`: Array of regex patterns matching `FQCN::methodName` (Fully Qualified Class Name and method name) that should have `@inheritDoc` in their docblocks.

## How It Works

The rule checks:

1. **Pattern Matching**: Methods are matched against the configured regex patterns using the format `FQCN::methodName` (e.g., `App\Controller\UserController::indexAction`).

2. **DocBlock Presence**: If a method matches a pattern, it must have a docblock. Methods without docblocks will trigger an error.

3. **@inheritDoc Annotation**: The docblock must contain the `@inheritDoc` or `@inheritdoc` annotation (case-insensitive) as an actual annotation, not just mentioned in descriptive text.

## Example Usage

### Valid Code

```php
class UserController extends BaseController
{
    /**
     * @inheritDoc
     */
    public function indexAction(): Response
    {
        return $this->render('index');
    }

    /**
     * Custom implementation
     *
     * @inheritdoc
     * @param string $id
     */
    public function showAction(string $id): Response
    {
        return $this->render('show', ['id' => $id]);
    }
}
```

### Invalid Code

```php
class UserController extends BaseController
{
    // Missing docblock entirely
    public function indexAction(): Response
    {
        return $this->render('index');
    }

    /**
     * This method does something
     */
    public function showAction(string $id): Response
    {
        return $this->render('show', ['id' => $id]);
    }
}
```

## Use Cases

- **Controllers**: Ensure controller actions document their inheritance from base controller methods.
- **Repositories**: Enforce documentation of inherited repository methods.
- **Services**: Require documentation for service methods that implement interfaces.
- **API Compliance**: Ensure methods implementing API contracts are properly documented.

## Notes

- The rule is case-insensitive for `@inheritDoc` vs `@inheritdoc`.
- The annotation must appear as an actual PHPDoc annotation (starting with `@` after the `*`), not just mentioned in descriptive text.
- Only methods matching the configured patterns are checked; other methods are ignored.

