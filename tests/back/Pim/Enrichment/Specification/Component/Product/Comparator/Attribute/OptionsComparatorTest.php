<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\OptionsComparator;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PHPUnit\Framework\TestCase;

class OptionsComparatorTest extends TestCase
{
    private OptionsComparator $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionsComparator();
    }

}
