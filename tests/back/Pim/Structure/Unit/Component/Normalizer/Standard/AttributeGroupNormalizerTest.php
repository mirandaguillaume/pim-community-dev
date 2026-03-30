<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeGroupNormalizer;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class AttributeGroupNormalizerTest extends TestCase
{
    private AttributeGroupNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroupNormalizer();
    }

}
