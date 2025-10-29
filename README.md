# PHPStan Rules

Additional rules for PHPStan, mostly focused on Clean Code and architecture conventions.

The rules help with enforcing certain method signatures, return types and dependency constraints in your codebase.

For example, you can configure the rules so that all controllers in your application must be `readonly`, no method should have more than 3 arguments, and no class should have more than 2 nested control structures.

## Usage

```bash
composer require phauthentic/phpstan-rules --dev
```

## Rules

See individual rule documentation for detailed configuration examples. A [full configuration example](docs/Rules.md) is also available.

### Architecture Rules

- [Dependency Constraints Rule](docs/rules/Dependency-Constraints-Rule.md)
- [Modular Architecture Rule](docs/rules/Modular-Architecture-Rule.md)
- [Circular Module Dependency Rule](docs/rules/Circular-Module-Dependency-Rule.md)
- [Forbidden Namespaces Rule](docs/rules/Forbidden-Namespaces-Rule.md)
- [Class Must Be Readonly Rule](docs/rules/Class-Must-Be-Readonly-Rule.md)
- [Class Must Be Final Rule](docs/rules/Class-Must-Be-Final-Rule.md)
- [Classname Must Match Pattern Rule](docs/rules/Classname-Must-Match-Pattern-Rule.md)
- [Catch Exception Of Type Not Allowed Rule](docs/rules/Catch-Exception-Of-Type-Not-Allowed-Rule.md)
- [Class Must Have Specification Docblock Rule](docs/rules/Class-Must-Have-Specification-Docblock-Rule.md)
- [Methods Returning Bool Must Follow Naming Convention Rule](docs/rules/Methods-Returning-Bool-Must-Follow-Naming-Convention-Rule.md)
- [Method Signature Must Match Rule](docs/rules/Method-Signature-Must-Match-Rule.md)
- [Method Must Return Type Rule](docs/rules/Method-Must-Return-Type-Rule.md)

### Clean Code Rules

- [Control Structure Nesting Rule](docs/rules/Control-Structure-Nesting-Rule.md)
- [Too Many Arguments Rule](docs/rules/Too-Many-Arguments-Rule.md)
- [Max Line Length Rule](docs/rules/Max-Line-Length-Rule.md)

### Using Regex in Rules

A lot of the rules use regex patterns to match things. Many people are not good at writing them but thankfully there is AI today.

If you struggle to write the regex patterns you need, you can use AI tools like [ChatGPT](https://chat.openai.com/) to help you generate them. Just describe what you want to match, and it can provide you with a regex pattern that fits your needs.  The regex can be tested using online tools like [regex101](https://regex101.com/).

## Why PHPStan to enforce Architectural Rules?

Because PHPStan is a widely used static analysis tool in the PHP community. It already provides a solid foundation for code quality checks, and adding custom rules allows you to enforce specific coding standards and architectural constraints is just a logical choice. You won't need more 3rd party tools to enforce your architectural constraints.

It is also more or less easy to write your own rules if you need to enforce something specific that is not covered by the existing rules.

### Alternative Tools

If you don't like this library, you can also check out other tools. Some of them provide a fluent interface instead of a Regex. If this feels more comfortable for you, you might want to check them out:

* [Deptrac](https://github.com/deptrac/deptrac) - Checks dependencies between namespaces and classes.
* [PHP Architecture Tester](https://www.phpat.dev/) - A tool to enforce architectural rules in PHP applications.
* [PHPArkitect](https://github.com/phparkitect/arkitect) - A tool to enforce architectural rules in PHP applications.
* [PHPMD](https://phpmd.org/) - A tool that scans PHP source code and looks for potential problems.

## License

This library is under the MIT license.

Copyright Florian Krämer