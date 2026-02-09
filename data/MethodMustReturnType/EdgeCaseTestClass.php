<?php

class EdgeCaseTestClass
{
    public function noReturnTypeWithType() { return 1; }

    public function noReturnTypeWithOneOf() { return 'test'; }

    public function noReturnTypeWithAllOf() { return 1; }

    public function objectReturnsInt(): int { return 1; }

    public function anyOfInvalid(): float { return 1.0; }

    public function anyOfValid(): int { return 1; }

    public function regexTypeValid(): SomeEdgeCaseObject { return new SomeEdgeCaseObject(); }

    public function regexTypeInvalid(): float { return 1.0; }

    public function validInt(): int { return 1; }
    public function validNullableString(): ?string { return null; }
    public function validVoid(): void { return; }
    public function validObject(): SomeEdgeCaseObject { return new SomeEdgeCaseObject(); }
    public function validNullableObject(): ?SomeEdgeCaseObject { return null; }
}

class SomeEdgeCaseObject {}
