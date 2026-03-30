<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\AttributeGroup;
use PHPUnit\Framework\TestCase;

class AttributeGroupTest extends TestCase
{
    private AttributeGroup $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroup();
    }

}
