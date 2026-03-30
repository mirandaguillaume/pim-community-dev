<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product\InitializeEvaluationOfAProductSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\GenericEvent;

class InitializeEvaluationOfAProductSubscriberTest extends TestCase
{
    private FeatureFlag|MockObject $dataQualityInsightsFeature;
    private CreateCriteriaEvaluations|MockObject $createProductsCriteriaEvaluations;
    private LoggerInterface|MockObject $logger;
    private ProductEntityIdFactoryInterface|MockObject $idFactory;
    private InitializeEvaluationOfAProductSubscriber $sut;

    protected function setUp(): void
    {
        $this->dataQualityInsightsFeature = $this->createMock(FeatureFlag::class);
        $this->createProductsCriteriaEvaluations = $this->createMock(CreateCriteriaEvaluations::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->idFactory = $this->createMock(ProductEntityIdFactoryInterface::class);
        $this->sut = new InitializeEvaluationOfAProductSubscriber(
            $this->dataQualityInsightsFeature,
            $this->createProductsCriteriaEvaluations,
            $this->logger,
            $this->idFactory
        );
    }

    public function test_it_does_nothing_when_the_entity_is_not_a_product(): void
    {
        $this->dataQualityInsightsFeature->expects($this->never())->method('isEnabled');
        $this->createProductsCriteriaEvaluations->expects($this->never())->method('createAll')->with($this->anything());
        $this->sut->onPostSave(new GenericEvent(new \stdClass()));
    }

    public function test_it_does_nothing_when_data_quality_insights_feature_is_not_active(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(false);
        $this->createProductsCriteriaEvaluations->expects($this->never())->method('createAll')->with($this->anything());
        $this->sut->onPostSave(new GenericEvent($product));
    }

    public function test_it_does_nothing_on_non_unitary_post_save(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->dataQualityInsightsFeature->expects($this->never())->method('isEnabled');
        $this->createProductsCriteriaEvaluations->expects($this->never())->method('createAll')->with($this->anything());
        $this->sut->onPostSave(new GenericEvent($product, ['unitary' => false]));
        $this->sut->onPostSave(new GenericEvent($product, []));
    }

    public function test_it_creates_criteria_on_unitary_product_post_save(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $productIdCollection = ProductUuidCollection::fromStrings(['54162e35-ff81-48f1-96d5-5febd3f00fd5']);
        $this->idFactory->method('createCollection')->with(['54162e35-ff81-48f1-96d5-5febd3f00fd5'])->willReturn($productIdCollection);
        $this->createProductsCriteriaEvaluations->expects($this->once())->method('createAll')->with($productIdCollection);
        $this->sut->onPostSave(new GenericEvent($product, ['unitary' => true]));
    }

    public function test_it_does_not_stop_the_process_if_something_goes_wrong(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $productUuidCollection = ProductUuidCollection::fromStrings(['54162e35-ff81-48f1-96d5-5febd3f00fd5']);
        $this->idFactory->method('createCollection')->with(['54162e35-ff81-48f1-96d5-5febd3f00fd5'])->willReturn($productUuidCollection);
        $this->createProductsCriteriaEvaluations->method('createAll')->with($productUuidCollection)->willThrowException(\Exception::class);
        $this->logger->expects($this->once())->method('error')->with('Unable to create product criteria evaluation', $this->anything());
        $this->sut->onPostSave(new GenericEvent($product, ['unitary' => true]));
    }
}
