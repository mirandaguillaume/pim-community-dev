<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\IdentifierType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class IdentifierTypeTest extends TestCase
{
    private IdentifierType $sut;

    protected function setUp(): void
    {
        $this->sut = new IdentifierType();
    }

}
