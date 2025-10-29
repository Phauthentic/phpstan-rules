# Method Must Return Type Rule

Ensures that methods matching a class and method name pattern have a specific return type, nullability, or are void. Supports union types with "oneOf" and "allOf" configurations.

## Configuration Example

```neon
    -
        class: Phauthentic\PHPStanRules\Architecture\MethodMustReturnTypeRule
        arguments:
            returnTypePatterns:
                -
                    pattern: '/^MyClass::getId$/'
                    type: 'int'
                    nullable: false
                    void: false
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::findUser$/'
                    type: 'object'
                    nullable: true
                    void: false
                    objectTypePattern: '/^App\\Entity\\User$/'
                -
                    pattern: '/^MyClass::reset$/'
                    type: 'void'
                    nullable: false
                    void: false
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::getValue$/'
                    nullable: false
                    void: false
                    oneOf: ['int', 'string', 'bool']
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::getUnionType$/'
                    nullable: false
                    void: false
                    allOf: ['int', 'string']
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::getAnyType$/'
                    anyOf: ['object', 'void']
                    objectTypePattern: null
                -
                    pattern: '/^MyClass::getEntity$/'
                    anyOf: ['regex:/^App\\Entity\\/', 'void']
                    objectTypePattern: null
        tags:
            - phpstan.rules.rule
```

## Parameters

- `pattern`: Regex for `ClassName::methodName`.
- `type`: Expected return type (`int`, `string`, `object`, `void`, etc.). When using `oneOf` or `allOf`, this field is optional.
- `nullable`: Whether the return type must be nullable.
- `void`: Legacy field for void return types (use `type: 'void'` instead).
- `objectTypePattern`: Regex for object return types (if `type` is `object`).
- `oneOf`: Array of types where one must match (for union types).
- `allOf`: Array of types where all must be present in the union type.
- `anyOf`: Alias for `oneOf` - array of types where one must match.

## Regex Support

You can use regex patterns in `oneOf`, `allOf`, and `anyOf` arrays by prefixing them with `regex:`. For example:

- `'regex:/^App\\Entity\\/'` - matches any class starting with "App\Entity\"
- `'regex:/^UserEntity$/'` - matches exactly "UserEntity"

