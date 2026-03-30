<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\CollectionNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CollectionNormalizerTest extends TestCase
{
    private CollectionNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new CollectionNormalizer();
    }

}
