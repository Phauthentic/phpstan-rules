# PHPStan Rules

Additional rules for PHPStan, mostly focused on Clean Code and architecture conventions.

## Usage

```bash
composer require phauthentic/phpstan-rules --dev
```

## Rules

See [Rules documentation](docs/Rules.md) for a list of available rules and configuration examples.

**Available Rules:**
- [Control Structure Nesting Rule](docs/Rules.md#control-structure-nesting-rule)
- [Too Many Arguments Rule](docs/Rules.md#too-many-arguments-rule)
- [Readonly Class Rule](docs/Rules.md#readonly-class-rule)
- [Dependency Constraints Rule](docs/Rules.md#dependency-constraints-rule)
- [Final Class Rule](docs/Rules.md#final-class-rule)
- [Namespace Class Pattern Rule](docs/Rules.md#namespace-class-pattern-rule)

### Using Regex in Rules

A lot of the rules use regex patterns to match things. Many people are not good at writing them but thankfully there is AI today.

If you struggle to write the regex patterns you need, you can use AI tools like [ChatGPT](https://chat.openai.com/) to help you generate them. Just describe what you want to match, and it can provide you with a regex pattern that fits your needs.  The regex can be tested using online tools like [regex101](https://regex101.com/).

## License

This library is under the MIT license.

Copyright Florian Krämer