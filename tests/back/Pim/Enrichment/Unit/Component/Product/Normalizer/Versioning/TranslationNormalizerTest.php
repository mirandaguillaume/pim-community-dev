<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use PHPUnit\Framework\TestCase;

class TranslationNormalizerTest extends TestCase
{
    private TranslationNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new TranslationNormalizer();
    }

}
