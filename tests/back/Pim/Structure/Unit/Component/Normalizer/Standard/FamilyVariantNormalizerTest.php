<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\FamilyVariantNormalizer;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyVariantNormalizerTest extends TestCase
{
    private FamilyVariantNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariantNormalizer();
    }

}
