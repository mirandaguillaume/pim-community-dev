<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\GroupNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup;
use Akeneo\Tool\Component\Versioning\Model\Version;
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
