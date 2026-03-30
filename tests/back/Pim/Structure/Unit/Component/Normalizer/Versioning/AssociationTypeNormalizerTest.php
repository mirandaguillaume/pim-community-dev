<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Versioning\AssociationTypeNormalizer;
use PHPUnit\Framework\TestCase;

class AssociationTypeNormalizerTest extends TestCase
{
    private AssociationTypeNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationTypeNormalizer();
    }

}
