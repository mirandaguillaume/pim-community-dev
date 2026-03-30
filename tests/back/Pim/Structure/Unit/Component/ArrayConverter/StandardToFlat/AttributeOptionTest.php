<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\AttributeOption;
use PHPUnit\Framework\TestCase;

class AttributeOptionTest extends TestCase
{
    private AttributeOption $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOption();
    }

}
