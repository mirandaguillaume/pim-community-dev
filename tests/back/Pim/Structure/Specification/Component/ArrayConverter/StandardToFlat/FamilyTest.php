<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\Family;
use PHPUnit\Framework\TestCase;

class FamilyTest extends TestCase
{
    private Family $sut;

    protected function setUp(): void
    {
        $this->sut = new Family();
    }

}
