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

## Why PHPStan to enforce Architectural Rules?

Because PHPStan is a widely used static analysis tool in the PHP community. It already provides a solid foundation for code quality checks, and adding custom rules allows you to enforce specific coding standards and architectural constraints is just a logical choice. You won't need more 3rd party tools to enforce your architectural constraints.

It is also more or less easy to write your own rules if you need to enforce something specific that is not covered by the existing rules.

### Alternative Tools

If you don't like this library, you can also check out other tools:

* [Deptrac](https://github.com/deptrac/deptrac)
* [PHP Architecture Tester](https://www.phpat.dev/)
* [PHPArkitect](https://github.com/phparkitect/arkitect)

## License

This library is under the MIT license.

Copyright Florian Krämer