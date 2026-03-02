<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\TranslationsUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;

class TranslationsUpdateGuesserSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['stdClass']);
    }

    public function it_is_an_update_guesser()
    {
        $this->shouldImplement(UpdateGuesserInterface::class);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TranslationsUpdateGuesser::class);
    }

    public function it_supports_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('foo')->shouldReturn(false);
    }

    public function it_supports_delete_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(true);
        $this->supportAction('bar')->shouldReturn(false);
    }

    public function it_guesses_translatable_entity_updates(
        EntityManager $em,
        UnitOfWork $uow,
        TranslatableEntity $entity,
        TranslationInterface $translation
    ) {
        $translation->getForeignKey()->willReturn($entity);

        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityState($entity)->willReturn(UnitOfWork::STATE_MANAGED);

        $this->guessUpdates($em, $translation, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$entity]);
    }

    public function it_returns_no_pending_updates_if_entity_state_is_removed(
        EntityManager $em,
        UnitOfWork $uow,
        TranslatableEntity $entity
    ) {
        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityState($entity)->willReturn(UnitOfWork::STATE_REMOVED);

        $this->guessUpdates($em, new \stdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }

    public function it_returns_no_pending_updates_if_not_given_versionable_class(
        EntityManager $em,
        UnitOfWork $uow,
        TranslationInterface $translation
    ) {
        $entity = new \stdClass();
        $translation->getForeignKey()->willReturn($entity);

        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityState($entity)->willReturn(UnitOfWork::STATE_REMOVED);

        $this->guessUpdates($em, $translation, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }
}

abstract class TranslatableEntity implements TranslatableInterface, VersionableInterface {}
