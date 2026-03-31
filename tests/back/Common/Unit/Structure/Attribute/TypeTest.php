<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Common\Structure\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Common\Structure\Attribute\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    private Type $sut;

    protected function setUp(): void
    {
        $this->sut = new Type();
    }

}
