<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

interface ProductEntityIdInterface extends \Stringable
{
    public function toBytes(): string;
}
