<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\FileComparator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PHPUnit\Framework\TestCase;

class FileComparatorTest extends TestCase
{
    private FileComparator $sut;

    protected function setUp(): void
    {
        $this->sut = new FileComparator();
    }

}
