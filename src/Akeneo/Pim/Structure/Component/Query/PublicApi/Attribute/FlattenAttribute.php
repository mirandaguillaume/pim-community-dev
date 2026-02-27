<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute;

class FlattenAttribute
{
    public function __construct(private readonly string $code, private readonly string $label, private readonly string $attributeGroupCode, private readonly string $attributeGroupLabel)
    {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getAttributeGroupCode(): string
    {
        return $this->attributeGroupCode;
    }

    public function getAttributeGroupLabel(): string
    {
        return $this->attributeGroupLabel;
    }
}
