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
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateAttributeRequirementSubscriberTest extends TestCase
{
    private CreateAttributeRequirementSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new CreateAttributeRequirementSubscriber();
    }

    public function test_it_ignores_non_ChannelInterface_entity(): void
    {
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $entityManager = $this->createMock(ObjectManager::class);

        $eventArgs->method('getObject')->willReturn(null);
        $entityManager->expects($this->never())->method('persist')->with($this->anything());
        $this->assertNull($this->sut->prePersist($eventArgs));
    }

    public function test_it_creates_requirements_for_the_attribute_defined_as_identifier(): void
    {
        $familyRepository = $this->createMock(FamilyRepositoryInterface::class);
        $attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $familyA = $this->createMock(FamilyInterface::class);
        $familyB = $this->createMock(FamilyInterface::class);
        $identifierAttribute = $this->createMock(AttributeInterface::class);
        $attributeRequirementA = $this->createMock(AttributeRequirementInterface::class);
        $attributeRequirementB = $this->createMock(AttributeRequirementInterface::class);

        $entityManager->getRepository(FamilyInterface::class)->willReturn($familyRepository);
        $entityManager->getRepository(AttributeInterface::class)->willReturn($attributeRepository);
        $familyRepository->method('findAll')->willReturn([$familyA, $familyB]);
        $attributeRepository->method('getIdentifier')->willReturn($identifierAttribute);
        $requirementFactory
                    ->createAttributeRequirement($identifierAttribute, $channel, true)
                    ->willReturn($attributeRequirementA, $attributeRequirementB);
        $attributeRequirementA->expects($this->once())->method('setFamily')->with($familyA);
        $attributeRequirementB->expects($this->once())->method('setFamily')->with($familyB);
        $entityManager->persist($attributeRequirementA)->shouldBeCalled();
        $entityManager->persist($attributeRequirementB)->shouldBeCalled();
        $this->assertNull($this->sut->prePersist($eventArgs));
    }
}
