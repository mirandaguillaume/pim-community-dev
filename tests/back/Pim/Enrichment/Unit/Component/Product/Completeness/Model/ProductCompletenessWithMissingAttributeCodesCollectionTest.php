<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductCompletenessWithMissingAttributeCodesCollectionTest extends TestCase
{
    private ProductCompletenessWithMissingAttributeCodesCollection $sut;

    protected function setUp(): void
    {
    }

    public function test_it_builds_product_was_completed_events(): void
    {
        $uuid = Uuid::uuid4();
        $changedAt = new \DateTimeImmutable('2022-10-23 12:45:21');
        $this->sut = new ProductCompletenessWithMissingAttributeCodesCollection($uuid->toString(), [
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 10, []),
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 10, []),
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'de_DE', 10, []),
                    new ProductCompletenessWithMissingAttributeCodes('mobile', 'en_US', 10, ['description', 'price']),
                    new ProductCompletenessWithMissingAttributeCodes('mobile', 'fr_FR', 10, ['description', 'price']),
                ]);
        $previousCompletenessCollection = new ProductCompletenessCollection($uuid, [
                    new ProductCompleteness('ecommerce', 'fr_FR', 10, 0),
                    new ProductCompleteness('ecommerce', 'en_US', 10, 2),
                    new ProductCompleteness('mobile', 'en_US', 10, 0),
                    new ProductCompleteness('mobile', 'fr_FR', 10, 2),
                ]);
        $productUuid = ProductUuid::fromUuid($uuid);
        $this->assertEquals([
                        new ProductWasCompletedOnChannelLocale($productUuid, $changedAt, 'ecommerce', 'en_US', '1'),
                        new ProductWasCompletedOnChannelLocale($productUuid, $changedAt, 'ecommerce', 'de_DE', '1'),
                    ], $this->sut->buildProductWasCompletedOnChannelLocaleEvents($changedAt, $previousCompletenessCollection, '1'));
    }

    public function test_it_does_not_build_any_events_if_no_product_was_completed(): void
    {
        $uuid = Uuid::uuid4();
        $changedAt = new \DateTimeImmutable('2022-10-23 12:45:21');
        $this->sut = new ProductCompletenessWithMissingAttributeCodesCollection($uuid->toString(), [
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 10, []),
                    new ProductCompletenessWithMissingAttributeCodes('mobile', 'en_US', 10, ['description', 'price']),
                ]);
        $previousCompletenessCollection = new ProductCompletenessCollection($uuid, [
                    new ProductCompleteness('ecommerce', 'en_US', 10, 0),
                    new ProductCompleteness('mobile', 'en_US', 10, 2),
                ]);
        $this->assertSame([], $this->sut->buildProductWasCompletedOnChannelLocaleEvents($changedAt, $previousCompletenessCollection, '1'));
    }
}
