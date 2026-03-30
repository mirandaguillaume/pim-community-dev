<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\OptionMultiSelectType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class OptionMultiSelectTypeTest extends TestCase
{
    private OptionMultiSelectType $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionMultiSelectType();
    }

}
