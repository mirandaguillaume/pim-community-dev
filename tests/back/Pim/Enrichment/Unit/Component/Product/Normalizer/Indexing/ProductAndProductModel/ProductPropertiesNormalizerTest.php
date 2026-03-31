<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductPropertiesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use PHPUnit\Framework\TestCase;

class ProductPropertiesNormalizerTest extends TestCase
{
    public function test_it_fails_if_an_extra_normalizer_is_not_a_normalizer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProductPropertiesNormalizer(
            $this->createMock(ChannelRepositoryInterface::class),
            $this->createMock(LocaleRepositoryInterface::class),
            $this->createMock(GetProductCompletenesses::class),
            $this->createMock(ValueCollectionNormalizer::class),
            [new \stdClass()]
        );
    }
}
