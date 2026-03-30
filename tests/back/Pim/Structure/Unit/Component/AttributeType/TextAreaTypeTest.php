<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\TextAreaType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class TextAreaTypeTest extends TestCase
{
    private TextAreaType $sut;

    protected function setUp(): void
    {
        $this->sut = new TextAreaType();
    }

}
