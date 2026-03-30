<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Converter\StandardToInternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Converter\StandardToInternalApi\ValueConverter;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ValueConverterTest extends TestCase
{
    private ValueConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new ValueConverter();
    }

}
