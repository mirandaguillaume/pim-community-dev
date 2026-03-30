<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\TextType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class TextTypeTest extends TestCase
{
    private TextType $sut;

    protected function setUp(): void
    {
        $this->sut = new TextType();
    }

}
