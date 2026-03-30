<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AvailableAttributes;
use PHPUnit\Framework\TestCase;

class AvailableAttributesTest extends TestCase
{
    private AvailableAttributes $sut;

    protected function setUp(): void
    {
        $this->sut = new AvailableAttributes();
    }

}
