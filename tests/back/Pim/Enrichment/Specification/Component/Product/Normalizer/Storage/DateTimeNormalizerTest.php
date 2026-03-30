<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Storage;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\DateTimeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerTest extends TestCase
{
    private DateTimeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new DateTimeNormalizer();
    }

}
