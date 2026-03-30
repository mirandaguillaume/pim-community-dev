<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant;

use Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant\FamilyVariant;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PHPUnit\Framework\TestCase;

class FamilyVariantTest extends TestCase
{
    private FamilyVariant $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariant();
    }

}
