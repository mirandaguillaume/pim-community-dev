<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Converter\InternalApiToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Converter\InternalApiToStandard\ValueConverter;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ValueConverterTest extends TestCase
{
    private ValueConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new ValueConverter();
    }

}
