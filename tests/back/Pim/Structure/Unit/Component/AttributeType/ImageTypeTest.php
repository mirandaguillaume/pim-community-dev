<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\ImageType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class ImageTypeTest extends TestCase
{
    private ImageType $sut;

    protected function setUp(): void
    {
        $this->sut = new ImageType();
    }

}
