<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeNormalizer;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use PHPUnit\Framework\TestCase;

class AttributeNormalizerTest extends TestCase
{
    private AttributeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeNormalizer();
    }

}
