<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class LaunchProductAndProductModelEvaluationsMessageTest extends TestCase
{
    private LaunchProductAndProductModelEvaluationsMessage $sut;

    protected function setUp(): void
    {
        $this->sut = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable(),
            ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed']),
            ProductModelIdCollection::fromStrings([]),
            []
        );
    }

    public function test_it_can_be_created_for_products_only(): void
    {
        $datetime = new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC'));
        $productUuids = ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de']);
        $this->sut = LaunchProductAndProductModelEvaluationsMessage::forProductsOnly($datetime, $productUuids, []);
        $this->assertSame($productUuids, $this->sut->productUuids);
        $this->assertSame(true, $this->sut->productModelIds->isEmpty());
    }

    public function test_it_can_be_created_for_product_models_only(): void
    {
        $datetime = new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC'));
        $productModelIds = ProductModelIdCollection::fromStrings(['42', '123']);
        $this->sut = LaunchProductAndProductModelEvaluationsMessage::forProductModelsOnly($datetime, $productModelIds, []);
        $this->assertSame($productModelIds, $this->sut->productModelIds);
        $this->assertSame(true, $this->sut->productUuids->isEmpty());
    }

    public function test_it_normalizes_itself(): void
    {
        $datetime = new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC'));
        $this->sut = new LaunchProductAndProductModelEvaluationsMessage(
            $datetime,
            ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de']),
            ProductModelIdCollection::fromStrings(['42', '123']),
            ['consistency_spelling']
        );
        $this->assertSame([
                    'datetime' => $datetime->format(\DateTimeInterface::ATOM),
                    'product_uuids' => ['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de'],
                    'product_model_ids' => ['42', '123'],
                    'criteria' => ['consistency_spelling'],
                ], $this->sut->normalize());
    }

    public function test_it_denormalizes_itself(): void
    {
        $datetime = new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC'));
        $message = LaunchProductAndProductModelEvaluationsMessage::denormalize([
                    'datetime' => $datetime->format(\DateTimeInterface::ATOM),
                    'product_uuids' => ['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de'],
                    'product_model_ids' => ['42', '123'],
                    'criteria' => ['consistency_spelling'],
                ]);
        Assert::eq(new LaunchProductAndProductModelEvaluationsMessage(
            $datetime,
            ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de']),
            ProductModelIdCollection::fromStrings(['42', '123']),
            ['consistency_spelling']
        ), $message);
    }

    public function test_it_throws_an_exception_if_there_is_nothing_to_evaluate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC')),
            ProductUuidCollection::fromStrings([]),
            ProductModelIdCollection::fromStrings([]),
            ['consistency_spelling']
        );
    }

    public function test_it_throws_an_exception_if_a_criteria_to_evaluate_has_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC')),
            ProductUuidCollection::fromStrings([]),
            ProductModelIdCollection::fromStrings(['42', '123']),
            ['consistency_spelling', 1234]
        );
    }
}
