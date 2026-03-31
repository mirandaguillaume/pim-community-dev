<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Subscriber\AttributeGroup;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\AttributeGroup\UpdateAttributeGroupActivationSubscriber;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class UpdateAttributeGroupActivationSubscriberTest extends TestCase
{
    private FeatureFlag|MockObject $dataQualityInsightsFeature;
    private AttributeGroupActivationRepositoryInterface|MockObject $attributeGroupActivationRepository;
    private GetAttributeGroupActivationQueryInterface|MockObject $getAttributeGroupActivationQuery;
    private LoggerInterface|MockObject $logger;
    private UpdateAttributeGroupActivationSubscriber $sut;

    protected function setUp(): void
    {
        $this->dataQualityInsightsFeature = $this->createMock(FeatureFlag::class);
        $this->attributeGroupActivationRepository = $this->createMock(AttributeGroupActivationRepositoryInterface::class);
        $this->getAttributeGroupActivationQuery = $this->createMock(GetAttributeGroupActivationQueryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new UpdateAttributeGroupActivationSubscriber($this->dataQualityInsightsFeature, $this->attributeGroupActivationRepository, $this->getAttributeGroupActivationQuery, $this->logger);
    }

    public function test_it_does_nothing_if_the_subject_is_not_an_attribute_group(): void
    {
        $event = new GenericEvent(new \stdClass());
        $this->attributeGroupActivationRepository->expects($this->never())->method('save')->with($this->anything());
        $this->attributeGroupActivationRepository->expects($this->never())->method('remove')->with($this->anything());
        $this->getAttributeGroupActivationQuery->expects($this->never())->method('byCode')->with($this->anything());
        $this->sut->createAttributeGroupActivation($event);
        $this->sut->removeAttributeGroupActivation($event);
    }

    public function test_it_does_nothing_if_the_feature_is_not_enabled(): void
    {
        $attributeGroup = $this->createMock(AttributeGroupInterface::class);

        $event = new GenericEvent($attributeGroup);
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(false);
        $this->attributeGroupActivationRepository->expects($this->never())->method('save')->with($this->anything());
        $this->attributeGroupActivationRepository->expects($this->never())->method('remove')->with($this->anything());
        $this->getAttributeGroupActivationQuery->expects($this->never())->method('byCode')->with($this->anything());
        $this->sut->createAttributeGroupActivation($event);
        $this->sut->removeAttributeGroupActivation($event);
    }

    public function test_it_does_not_create_attribute_group_activation_if_there_is_already_one(): void
    {
        $attributeGroup = $this->createMock(AttributeGroupInterface::class);

        $event = new GenericEvent($attributeGroup);
        $attributeGroupCode = new AttributeGroupCode('marketing');
        $attributeGroup->method('getCode')->willReturn('marketing');
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $this->getAttributeGroupActivationQuery->method('byCode')->with($attributeGroupCode)->willReturn(new AttributeGroupActivation($attributeGroupCode, false));
        $this->attributeGroupActivationRepository->expects($this->never())->method('save')->with($this->anything());
        $this->sut->createAttributeGroupActivation($event);
    }

    public function test_it_creates_new_attribute_group_activation(): void
    {
        $attributeGroup = $this->createMock(AttributeGroupInterface::class);

        $event = new GenericEvent($attributeGroup);
        $attributeGroup->method('getCode')->willReturn('marketing');
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $this->getAttributeGroupActivationQuery->method('byCode')->with(new AttributeGroupCode('marketing'))->willReturn(null);
        $this->attributeGroupActivationRepository->expects($this->once())->method('save')->with($this->callback(fn (AttributeGroupActivation $attributeGroupActivation) => 'marketing' === strval($attributeGroupActivation->getAttributeGroupCode())
                    && true === $attributeGroupActivation->isActivated()));
        $this->sut->createAttributeGroupActivation($event);
    }

    public function test_it_removes_attribute_group_activation(): void
    {
        $attributeGroup = $this->createMock(AttributeGroupInterface::class);

        $event = new GenericEvent($attributeGroup);
        $attributeGroup->method('getCode')->willReturn('marketing');
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $this->attributeGroupActivationRepository->expects($this->once())->method('remove')->with(new AttributeGroupCode('marketing'));
        $this->sut->removeAttributeGroupActivation($event);
    }

    public function test_it_does_not_crash_if_the_repository_fails(): void
    {
        $attributeGroup = $this->createMock(AttributeGroupInterface::class);

        $event = new GenericEvent($attributeGroup);
        $attributeGroup->method('getCode')->willReturn('marketing');
        $this->dataQualityInsightsFeature->method('isEnabled')->willReturn(true);
        $this->getAttributeGroupActivationQuery->method('byCode')->with(new AttributeGroupCode('marketing'))->willReturn(null);
        $this->attributeGroupActivationRepository->method('save')->with($this->anything())->willThrowException(new \Exception('failed'));
        $this->attributeGroupActivationRepository->method('remove')->with($this->anything())->willThrowException(new \Exception('failed'));
        $this->logger->expects($this->exactly(2))->method('error');
        $this->sut->createAttributeGroupActivation($event);
        $this->sut->removeAttributeGroupActivation($event);
    }
}
