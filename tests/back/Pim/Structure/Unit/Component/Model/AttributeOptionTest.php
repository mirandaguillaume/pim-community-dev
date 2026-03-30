<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use PHPUnit\Framework\TestCase;

class AttributeOptionTest extends TestCase
{
    private AttributeOption $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOption();
    }

}
