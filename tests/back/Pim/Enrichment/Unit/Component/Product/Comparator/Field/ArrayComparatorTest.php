<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Comparator\Field;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Field\ArrayComparator;
use PHPUnit\Framework\TestCase;

class ArrayComparatorTest extends TestCase
{
    private ArrayComparator $sut;

    protected function setUp(): void
    {
        $this->sut = new ArrayComparator();
    }

}
