<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\OptionSimpleSelectType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class OptionSimpleSelectTypeTest extends TestCase
{
    private OptionSimpleSelectType $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionSimpleSelectType();
    }

}
