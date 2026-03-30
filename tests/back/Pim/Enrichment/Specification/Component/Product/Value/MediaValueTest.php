<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PHPUnit\Framework\TestCase;

class MediaValueTest extends TestCase
{
    private MediaValue $sut;

    protected function setUp(): void
    {
        $this->sut = new MediaValue();
    }

}
