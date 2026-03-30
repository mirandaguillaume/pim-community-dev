<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class ValuesNormalizerTest extends TestCase
{
    private ValuesNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ValuesNormalizer();
    }

}
