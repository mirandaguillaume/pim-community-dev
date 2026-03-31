<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Bundle\EventSubscriber\CreateAttributeRequirementSubscriber;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateAttributeRequirementSubscriberTest extends TestCase
{
    private AttributeRequirementFactory|MockObject $requirementFactory;
    private CreateAttributeRequirementSubscriber $sut;

    protected function setUp(): void
    {
        $this->requirementFactory = $this->createMock(AttributeRequirementFactory::class);
        $this->sut = new CreateAttributeRequirementSubscriber($this->requirementFactory);
    }

    public function test_it_ignores_non_ChannelInterface_entity(): void
    {
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $eventArgs->method('getObject')->willReturn(new \stdClass());
        $this->assertNull($this->sut->prePersist($eventArgs));
    }

    public function test_it_creates_requirements_for_the_attribute_defined_as_identifier(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $entityManager = $this->createMock(ObjectManager::class);
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $eventArgs->method('getObject')->willReturn($channel);
        $eventArgs->method('getObjectManager')->willReturn($entityManager);

        $familyA = $this->createMock(FamilyInterface::class);
        $familyB = $this->createMock(FamilyInterface::class);
        $identifierAttribute = $this->createMock(AttributeInterface::class);
        $attributeRequirementA = $this->createMock(AttributeRequirementInterface::class);
        $attributeRequirementB = $this->createMock(AttributeRequirementInterface::class);

        $familyRepository = $this->createMock(ObjectRepository::class);
        $attributeRepository = $this->createMock(AttributeRepositoryInterface::class);

        $entityManager->method('getRepository')->willReturnMap([
            [FamilyInterface::class, $familyRepository],
            [AttributeInterface::class, $attributeRepository],
        ]);

        $familyRepository->method('findAll')->willReturn([$familyA, $familyB]);
        $attributeRepository->method('getIdentifier')->willReturn($identifierAttribute);

        $this->requirementFactory->method('createAttributeRequirement')
            ->with($identifierAttribute, $channel, true)
            ->willReturnOnConsecutiveCalls($attributeRequirementA, $attributeRequirementB);

        $attributeRequirementA->expects($this->once())->method('setFamily')->with($familyA);
        $attributeRequirementB->expects($this->once())->method('setFamily')->with($familyB);
        $entityManager->expects($this->exactly(2))->method('persist');

        $this->sut->prePersist($eventArgs);
    }
}
