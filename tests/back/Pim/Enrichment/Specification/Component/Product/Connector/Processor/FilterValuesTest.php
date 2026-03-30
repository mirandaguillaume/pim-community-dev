<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\FilterValues;
use PHPUnit\Framework\TestCase;

class FilterValuesTest extends TestCase
{
    private FilterValues $sut;

    protected function setUp(): void
    {
        $this->sut = new FilterValues();
        $this->sut->beConstructedThrough('create');
    }

}
