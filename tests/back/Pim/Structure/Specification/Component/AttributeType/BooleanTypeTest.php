<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\BooleanType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class BooleanTypeTest extends TestCase
{
    private BooleanType $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanType();
    }

}
