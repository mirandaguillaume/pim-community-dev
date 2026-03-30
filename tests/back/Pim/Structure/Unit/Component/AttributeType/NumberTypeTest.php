<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\NumberType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class NumberTypeTest extends TestCase
{
    private NumberType $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberType();
    }

}
