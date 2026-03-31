<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\FamilyAttributeRequirementUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FamilyAttributeRequirementUpdateGuesserTest extends TestCase
{
    private FamilyAttributeRequirementUpdateGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyAttributeRequirementUpdateGuesser();
    }

    public function test_it_is_an_update_guesser(): void
    {
        $this->assertInstanceOf(UpdateGuesserInterface::class, $this->sut);
    }

    public function test_it_supports_update_action(): void
    {
        $this->assertSame(true, $this->sut->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
        $this->assertSame(false, $this->sut->supportAction('foo'));
    }

    public function test_it_supports_delete_action(): void
    {
        $this->assertSame(true, $this->sut->supportAction(UpdateGuesserInterface::ACTION_DELETE));
    }

    public function test_it_guesses_family_update_when_an_attribute_requirement_is_added(): void
    {
        $em = $this->createMock(EntityManager::class);
        $uo = $this->createMock(UnitOfWork::class);
        $attributeRequirement = $this->createMock(AttributeRequirement::class);
        $family = $this->createMock(Family::class);

        $em->method('getUnitOfWork')->willReturn($uo);
        $uo->method('getEntityState')->with($family)->willReturn(UnitOfWork::STATE_MANAGED);
        $attributeRequirement->method('getFamily')->willReturn($family);
        $this->assertSame([$family], $this->sut->guessUpdates($em, $attributeRequirement, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }

    public function test_it_guesses_family_update_when_an_attribute_requirement_is_removed(): void
    {
        $em = $this->createMock(EntityManager::class);
        $uo = $this->createMock(UnitOfWork::class);
        $attributeRequirement = $this->createMock(AttributeRequirement::class);
        $family = $this->createMock(Family::class);

        $em->method('getUnitOfWork')->willReturn($uo);
        $uo->method('getEntityState')->with($family)->willReturn(UnitOfWork::STATE_MANAGED);
        $attributeRequirement->method('getFamily')->willReturn($family);
        $this->assertSame([$family], $this->sut->guessUpdates($em, $attributeRequirement, UpdateGuesserInterface::ACTION_DELETE));
    }

    public function test_it_returns_no_pending_update_if_family_is_deleted_too(): void
    {
        $em = $this->createMock(EntityManager::class);
        $uo = $this->createMock(UnitOfWork::class);
        $attributeRequirement = $this->createMock(AttributeRequirement::class);
        $family = $this->createMock(Family::class);

        $em->method('getUnitOfWork')->willReturn($uo);
        $uo->method('getEntityState')->with($family)->willReturn(UnitOfWork::STATE_REMOVED);
        $attributeRequirement->method('getFamily')->willReturn($family);
        $this->assertSame([], $this->sut->guessUpdates($em, $attributeRequirement, UpdateGuesserInterface::ACTION_DELETE));
    }

    public function test_it_returns_no_pending_updates_if_not_given_an_attribute_requirement(): void
    {
        $em = $this->createMock(EntityManager::class);

        $this->assertSame([], $this->sut->guessUpdates($em, new Family(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }
}
