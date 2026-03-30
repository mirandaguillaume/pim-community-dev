<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\FamilyVariantNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyVariantNormalizerTest extends TestCase
{
    private FamilyVariantNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariantNormalizer();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}
