<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\Attribute;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    private Attribute $sut;

    protected function setUp(): void
    {
        $this->sut = new Attribute();
    }

}
