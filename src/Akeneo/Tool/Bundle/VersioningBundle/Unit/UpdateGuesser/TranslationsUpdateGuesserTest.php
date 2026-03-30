<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\TranslationsUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslationsUpdateGuesserTest extends TestCase
{
    private TranslationsUpdateGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new TranslationsUpdateGuesser(['stdClass']);
    }

    public function test_it_is_an_update_guesser(): void
    {
        $this->assertInstanceOf(UpdateGuesserInterface::class, $this->sut);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(TranslationsUpdateGuesser::class, $this->sut);
    }

    public function test_it_supports_update_action(): void
    {
        $this->assertSame(true, $this->sut->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
        $this->assertSame(false, $this->sut->supportAction('foo'));
    }

    public function test_it_supports_delete_action(): void
    {
        $this->assertSame(true, $this->sut->supportAction(UpdateGuesserInterface::ACTION_DELETE));
        $this->assertSame(false, $this->sut->supportAction('bar'));
    }

    public function test_it_guesses_translatable_entity_updates(): void
    {
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);
        $entity = $this->createMock(TranslatableEntity::class);
        $translation = $this->createMock(TranslationInterface::class);

        $translation->method('getForeignKey')->willReturn($entity);
        $em->method('getUnitOfWork')->willReturn($uow);
        $uow->method('getEntityState')->with($entity)->willReturn(UnitOfWork::STATE_MANAGED);
        $this->assertSame([$entity], $this->sut->guessUpdates($em, $translation, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }

    public function test_it_returns_no_pending_updates_if_entity_state_is_removed(): void
    {
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);
        $entity = $this->createMock(TranslatableEntity::class);

        $em->method('getUnitOfWork')->willReturn($uow);
        $uow->method('getEntityState')->with($entity)->willReturn(UnitOfWork::STATE_REMOVED);
        $this->assertSame([], $this->sut->guessUpdates($em, new \stdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }

    public function test_it_returns_no_pending_updates_if_not_given_versionable_class(): void
    {
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);
        $translation = $this->createMock(TranslationInterface::class);

        $entity = new \stdClass();
        $translation->method('getForeignKey')->willReturn($entity);
        $em->method('getUnitOfWork')->willReturn($uow);
        $uow->method('getEntityState')->with($entity)->willReturn(UnitOfWork::STATE_REMOVED);
        $this->assertSame([], $this->sut->guessUpdates($em, $translation, UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }
}
