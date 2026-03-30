<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessageNormalizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LaunchProductAndProductModelEvaluationsMessageNormalizerTest extends TestCase
{
    private LaunchProductAndProductModelEvaluationsMessageNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new LaunchProductAndProductModelEvaluationsMessageNormalizer();
    }

    public function test_it_is_a_normalizer_and_denormalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
        $this->assertInstanceOf(DenormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_denormalization_of_launch_product_and_product_model_evaluations_message(): void
    {
        $this->assertSame(true, $this->sut->supportsDenormalization([], LaunchProductAndProductModelEvaluationsMessage::class));
    }

    public function test_it_supports_normalization_of_launch_product_and_product_model_evaluations_message(): void
    {
        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable(),
            ProductUuidCollection::fromProductUuids([
                        ProductUuid::fromUuid(Uuid::uuid4()),
                    ]),
            ProductModelIdCollection::fromProductModelIds([]),
            []
        );
        $this->assertSame(true, $this->sut->supportsNormalization($message));
    }

    public function test_it_throws_an_exception_if_the_object_to_normalize_is_not_supported(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->normalize(new \stdClass());
    }
}
