<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Association;

class AssociationType
{
    public function __construct(private readonly string $code, private readonly LabelCollection $labels, private readonly bool $isTwoWay, private readonly bool $isQuantified) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(string $localeCode): string
    {
        return $this->labels->getLabel($localeCode);
    }

    public function isTwoWay(): bool
    {
        return $this->isTwoWay;
    }

    public function isQuantified(): bool
    {
        return $this->isQuantified;
    }
}
