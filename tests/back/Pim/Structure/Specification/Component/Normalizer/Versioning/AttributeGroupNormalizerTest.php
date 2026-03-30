<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Versioning\AttributeGroupNormalizer;
use PHPUnit\Framework\TestCase;

class AttributeGroupNormalizerTest extends TestCase
{
    private AttributeGroupNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroupNormalizer();
    }

}
