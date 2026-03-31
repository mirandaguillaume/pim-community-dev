<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\ProductModel\InitializeEvaluationOfAProductModelSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InitializeEvaluationOfAProductModelSubscriberTest extends TestCase
{
    private FeatureFlag|MockObject $dataQualityInsightsFeature;
    private CreateCriteriaEvaluations|MockObject $createCriteriaEvaluations;
    private LoggerInterface|MockObject $logger;
    private ProductEntityIdFactoryInterface|MockObject $idFactory;
    private InitializeEvaluationOfAProductModelSubscriber $sut;

    protected function setUp(): void
    {
        $this->dataQualityInsightsFeature = $this->createMock(FeatureFlag::class);
        $this->createCriteriaEvaluations = $this->createMock(CreateCriteriaEvaluations::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->idFactory = $this->createMock(ProductEntityIdFactoryInterface::class);
        $this->sut = new InitializeEvaluationOfAProductModelSubscriber(
            $this->dataQualityInsightsFeature,
            $this->createCriteriaEvaluations,
            $this->logger,
            $this->idFactory
        );
    }

    public function test_it_does_nothing_when_the_entity_is_not_a_product(): void
    {
        $this->dataQualityInsightsFeature->expects($this->never())->method('isEnabled');
        $this->createCriteriaEvaluations->expects($this->never())->method('createAll')->with($this->anything());
        $this->sut->onPostSave(new GenericEvent(new \stdClass()));
    }

    public function test_it_does_nothing_when_data_quality_insights_feature_is_not_active(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);

        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(false);
        $this->createCriteriaEvaluations->expects($this->never())->method('createAll')->with($this->anything());
        $this->sut->onPostSave(new GenericEvent($productModel));
    }

    public function test_it_does_nothing_on_non_unitary_post_save(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);

        $this->dataQualityInsightsFeature->expects($this->never())->method('isEnabled');
        $this->createCriteriaEvaluations->expects($this->never())->method('createAll')->with($this->anything());
        $this->sut->onPostSave(new GenericEvent($productModel, ['unitary' => false]));
        $this->sut->onPostSave(new GenericEvent($productModel, []));
    }

    public function test_it_creates_criteria_on_unitary_product_post_save(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);

        $productModelId = 12345;
        $productModelIdCollection = ProductModelIdCollection::fromStrings([(string) $productModelId]);
        $productModel->method('getId')->willReturn($productModelId);
        $this->idFactory->method('createCollection')->with([(string) $productModelId])->willReturn($productModelIdCollection);
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $this->createCriteriaEvaluations->expects($this->once())->method('createAll')->with($productModelIdCollection);
        $this->sut->onPostSave(new GenericEvent($productModel, ['unitary' => true]));
    }

    public function test_it_does_not_stop_the_process_if_something_goes_wrong(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);

        $productModelId = 12345;
        $productModelIdCollection = ProductModelIdCollection::fromStrings([(string) $productModelId]);
        $productModel->method('getId')->willReturn($productModelId);
        $this->idFactory->method('createCollection')->with([(string) $productModelId])->willReturn($productModelIdCollection);
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $this->createCriteriaEvaluations->method('createAll')->with($productModelIdCollection)->willThrowException(new \Exception('test'));
        $this->logger->expects($this->once())->method('error')->with('Unable to create product model criteria evaluation', $this->anything());
        $this->sut->onPostSave(new GenericEvent($productModel, ['unitary' => true]));
    }
}
