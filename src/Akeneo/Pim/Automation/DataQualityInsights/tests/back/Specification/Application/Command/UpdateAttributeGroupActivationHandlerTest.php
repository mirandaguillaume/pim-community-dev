<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Command\UpdateAttributeGroupActivationCommand;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Command\UpdateAttributeGroupActivationHandler;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\AttributeGroupActivationHasChanged;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateAttributeGroupActivationHandlerTest extends TestCase
{
    private AttributeGroupActivationRepositoryInterface|MockObject $attributeGroupActivationRepository;
    private MessageBusInterface|MockObject $messageBus;
    private Clock|MockObject $clock;
    private FeatureFlag|MockObject $dqiUcsEventFeatureFlag;
    private UpdateAttributeGroupActivationHandler $sut;

    protected function setUp(): void
    {
        $this->attributeGroupActivationRepository = $this->createMock(AttributeGroupActivationRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->clock = $this->createMock(Clock::class);
        $this->dqiUcsEventFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->sut = new UpdateAttributeGroupActivationHandler($this->attributeGroupActivationRepository, $this->messageBus, $this->clock, $this->dqiUcsEventFeatureFlag);
        $this->dqiUcsEventFeatureFlag->method('isEnabled')->willReturn(true);
    }

    public function test_it_saves_an_attribute_group_activation(): void
    {
        $command = new UpdateAttributeGroupActivationCommand('code', true);
        $attributeGroupCode = new AttributeGroupCode('code');
        $this->attributeGroupActivationRepository->expects($this->once())->method('getForAttributeGroupCode')->with($attributeGroupCode)->willReturn(null);
        $this->attributeGroupActivationRepository->expects($this->once())->method('save')->with(new AttributeGroupActivation($attributeGroupCode, true));
        $date = new \DateTimeImmutable();
        $this->clock->method('getCurrentTime')->willReturn($date);
        $this->messageBus->expects($this->once())->method('dispatch')->with(new AttributeGroupActivationHasChanged('code', true, $date))->willReturn(new Envelope(new \stdClass()));
        $this->sut->__invoke($command);
    }

    public function test_it_does_not_save_an_attribute_group_activation_if_the_attribute_group_is_already_activated(): void
    {
        $command = new UpdateAttributeGroupActivationCommand('code', true);
        $attributeGroupCode = new AttributeGroupCode('code');
        $this->attributeGroupActivationRepository->expects($this->once())->method('getForAttributeGroupCode')->with($attributeGroupCode)->willReturn(new AttributeGroupActivation($attributeGroupCode, true));
        $this->attributeGroupActivationRepository->expects($this->never())->method('save')->with($this->anything());
        $this->messageBus->expects($this->never())->method('dispatch')->with($this->anything());
        $this->sut->__invoke($command);
    }

    public function test_it_saves_an_attribute_group_deactivation(): void
    {
        $command = new UpdateAttributeGroupActivationCommand('code', false);
        $attributeGroupCode = new AttributeGroupCode('code');
        $this->attributeGroupActivationRepository->expects($this->once())->method('getForAttributeGroupCode')->with($attributeGroupCode)->willReturn(new AttributeGroupActivation($attributeGroupCode, true));
        $this->attributeGroupActivationRepository->expects($this->once())->method('save')->with(new AttributeGroupActivation($attributeGroupCode, false));
        $date = new \DateTimeImmutable();
        $this->clock->method('getCurrentTime')->willReturn($date);
        $this->messageBus->expects($this->once())->method('dispatch')->with(new AttributeGroupActivationHasChanged('code', false, $date))->willReturn(new Envelope(new \stdClass()));
        $this->sut->__invoke($command);
    }

    public function test_it_does_not_save_an_attribute_group_deactivation_if_the_attribute_group_is_already_deactivated(): void
    {
        $command = new UpdateAttributeGroupActivationCommand('code', false);
        $attributeGroupCode = new AttributeGroupCode('code');
        $this->attributeGroupActivationRepository->expects($this->once())->method('getForAttributeGroupCode')->with($attributeGroupCode)->willReturn(new AttributeGroupActivation($attributeGroupCode, false));
        $this->attributeGroupActivationRepository->expects($this->never())->method('save')->with($this->anything());
        $this->messageBus->expects($this->never())->method('dispatch')->with($this->anything());
        $this->sut->__invoke($command);
    }

    public function test_it_does_not_save_an_attribute_group_deactivation_if_the_attribute_group_is_not_present_in_database(): void
    {
        $command = new UpdateAttributeGroupActivationCommand('code', false);
        $attributeGroupCode = new AttributeGroupCode('code');
        $this->attributeGroupActivationRepository->expects($this->once())->method('getForAttributeGroupCode')->with($attributeGroupCode)->willReturn(null);
        $this->attributeGroupActivationRepository->expects($this->never())->method('save')->with($this->anything());
        $this->messageBus->expects($this->never())->method('dispatch')->with($this->anything());
        $this->sut->__invoke($command);
    }

    public function test_it_does_not_dispatches_message_if_the_ucs_event_feature_is_disabled(): void
    {
        $this->dqiUcsEventFeatureFlag->method('isEnabled')->willReturn(false);
        $this->sut = new UpdateAttributeGroupActivationHandler($this->attributeGroupActivationRepository, $this->messageBus, $this->clock, $this->dqiUcsEventFeatureFlag);
        $command = new UpdateAttributeGroupActivationCommand('code', true);
        $attributeGroupCode = new AttributeGroupCode('code');
        $this->attributeGroupActivationRepository->expects($this->once())->method('getForAttributeGroupCode')->with($attributeGroupCode)->willReturn(null);
        $this->attributeGroupActivationRepository->expects($this->once())->method('save')->with(new AttributeGroupActivation($attributeGroupCode, true));
        $date = new \DateTimeImmutable();
        $this->clock->method('getCurrentTime')->willReturn($date);
        $this->messageBus->expects($this->never())->method('dispatch')->with($this->anything());
        $this->sut->__invoke($command);
    }
}
