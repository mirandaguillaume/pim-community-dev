<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\GroupNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerTest extends TestCase
{
    private GroupNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupNormalizer();
    }

}
