<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Versioning\FamilyNormalizer;
use PHPUnit\Framework\TestCase;

class FamilyNormalizerTest extends TestCase
{
    private FamilyNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyNormalizer();
    }

}
