<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\AssociationTypeNormalizer;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\TestCase;

class AssociationTypeNormalizerTest extends TestCase
{
    private AssociationTypeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationTypeNormalizer();
    }

}
