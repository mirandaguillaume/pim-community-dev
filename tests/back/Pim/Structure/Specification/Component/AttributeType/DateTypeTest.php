<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\DateType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class DateTypeTest extends TestCase
{
    private DateType $sut;

    protected function setUp(): void
    {
        $this->sut = new DateType();
    }

}
