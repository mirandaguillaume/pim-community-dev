<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\GroupType;
use PHPUnit\Framework\TestCase;

class GroupTypeTest extends TestCase
{
    private GroupType $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupType();
    }

}
