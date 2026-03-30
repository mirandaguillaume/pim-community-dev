<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Normalizer;

use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Normalizer\ProductsWereCreatedOrUpdatedNormalizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductsWereCreatedOrUpdatedNormalizerTest extends TestCase
{
    private ProductsWereCreatedOrUpdatedNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductsWereCreatedOrUpdatedNormalizer();
    }

    public function test_it_supports_products_were_created_or_updated_objects_for_normalization(): void
    {
        $object = new ProductsWereCreatedOrUpdated([
                    new ProductWasCreated(Uuid::uuid4(), new \DateTimeImmutable()),
                ]);
        $this->assertSame(true, $this->sut->supportsNormalization($object));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass()));
    }

    public function test_it_normalizes_products_were_created_or_updated_object(): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $object = new ProductsWereCreatedOrUpdated([
                    new ProductWasCreated($uuid1, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-26 02:05:27')),
                    new ProductWasUpdated($uuid2, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-27 02:05:27')),
                ]);
        $this->assertSame([
                    'events' => [
                        ['product_uuid' => $uuid1->toString(), 'created_at' => '2023-06-26T02:05:27+00:00'],
                        ['product_uuid' => $uuid2->toString(), 'updated_at' => '2023-06-27T02:05:27+00:00'],
                    ],
                ], $this->sut->normalize($object));
    }

    public function test_it_supports_products_were_created_or_updated_objects_for_denormalization(): void
    {
        $object = new ProductsWereCreatedOrUpdated([
                    new ProductWasCreated(Uuid::uuid4(), new \DateTimeImmutable()),
                ]);
        $this->assertSame(true, $this->sut->supportsDenormalization(null, ProductsWereCreatedOrUpdated::class));
        $this->assertSame(false, $this->sut->supportsDenormalization(null, 'Other'));
    }

    public function test_it_denormalizes_products_were_created_or_updated_object(): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $object = new ProductsWereCreatedOrUpdated([
                    new ProductWasCreated($uuid1, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-26 02:05:27')),
                    new ProductWasUpdated($uuid2, \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-06-27 02:05:27')),
                ]);
        $normalized = [
                    'events' => [
                        ['product_uuid' => $uuid1->toString(), 'created_at' => '2023-06-26T02:05:27+00:00'],
                        ['product_uuid' => $uuid2->toString(), 'updated_at' => '2023-06-27T02:05:27+00:00'],
                    ],
                ];
        $this->assertEquals($object, $this->sut->denormalize($normalized, ProductsWereCreatedOrUpdated::class));
    }
}
