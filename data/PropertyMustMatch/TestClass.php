<?php

declare(strict_types=1);

class DummyRepository
{
}

// Valid controller with all required properties
class ValidController
{
    private int $id;
    private DummyRepository $repository;
}

// Missing required property 'id'
class MissingPropertyController
{
    private DummyRepository $repository;
}

// Wrong type for 'id' property (string instead of int)
class WrongTypeController
{
    private string $id;
    private DummyRepository $repository;
}

// Wrong visibility for 'id' property (public instead of private)
class WrongVisibilityController
{
    public int $id;
    private DummyRepository $repository;
}

// Multiple errors: wrong type and wrong visibility
class MultipleErrorsController
{
    public string $id;
    protected DummyRepository $repository;
}

// Missing type on property
class NoTypeController
{
    private $id;
    private DummyRepository $repository;
}

// Nullable type property
class NullableTypeController
{
    private ?int $id;
    private DummyRepository $repository;
}

// Service class with optional logger property (not required)
class ValidService
{
    private LoggerInterface $logger;
}

// Service class without logger (should be fine since not required)
class ServiceWithoutLogger
{
    private string $name;
}

// Service class with wrong logger type
class WrongLoggerTypeService
{
    private string $logger;
}

// Class that doesn't match any pattern (should be ignored)
class NotMatchingClass
{
    public string $id;
    public int $logger;
}

// NullableAllowed classes - used to test nullable: true flag
// Has nullable type, should pass when nullable: true
class NullableAllowedHandler
{
    private ?int $id;
}

// Has non-nullable type, should also pass when nullable: true
class NonNullableAllowedHandler
{
    private int $id;
}

// Has wrong type entirely, should fail even with nullable: true
class WrongTypeAllowedHandler
{
    private string $id;
}
