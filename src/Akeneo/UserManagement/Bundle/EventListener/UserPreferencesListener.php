<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

/**
 * Aims to add/remove locales, channels and trees to user preference choices
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesListener
{
    /** @var array */
    protected $metadata = [];

    /** @var array */
    protected $deactivatedLocales = [];

    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository, private readonly ChannelRepositoryInterface $channelRepository, private readonly LocaleRepositoryInterface $localeRepository, private readonly UserRepositoryInterface $userRepository) {}

    /**
     * On flush
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $manager = $args->getObjectManager();
        $uow = $manager->getUnitOfWork();
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->preUpdate($uow, $entity);
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->preRemove($uow, $manager, $entity);
        }
    }

    /**
     * Post flush
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $manager = $args->getObjectManager();

        if (!empty($this->deactivatedLocales)) {
            $this->onLocalesDeactivated($manager);
        }
    }

    /**
     * Before remove
     *
     * @param object                 $entity
     */
    protected function preRemove(UnitOfWork $uow, EntityManagerInterface $manager, $entity)
    {
        if ($entity instanceof ChannelInterface) {
            $this->onChannelRemoved($uow, $manager, $entity);
        }

        if ($entity instanceof CategoryInterface && $entity->isRoot()) {
            $this->onTreeRemoved($uow, $manager, $entity);
        }
    }

    /**
     * Before update
     *
     * @param object $entity
     */
    protected function preUpdate(UnitOfWork $uow, $entity)
    {
        if ($entity instanceof LocaleInterface && !$entity->isActivated()) {
            $changeset = $uow->getEntityChangeSet($entity);
            if (isset($changeset['activated'])) {
                $this->deactivatedLocales[] = $entity->getCode();
            }
        }
    }

    /**
     * Get the metadata of an entity
     *
     * @param object                 $entity
     * @return array
     */
    protected function getMetadata(EntityManagerInterface $manager, $entity)
    {
        $className = $entity::class;
        if (!isset($this->metadata[$className])) {
            $this->metadata[$className] = $manager->getClassMetadata($className);
        }

        return $this->metadata[$className];
    }

    /**
     * Compute changeset
     *
     * @param object                 $entity
     */
    protected function computeChangeset(UnitOfWork $uow, EntityManagerInterface $manager, $entity)
    {
        $uow->persist($entity);
        $uow->computeChangeSet($this->getMetadata($manager, $entity), $entity);
    }

    /**
     * Update catalog scope of users using a channel that will be removed
     */
    protected function onChannelRemoved(
        UnitOfWork $uow,
        EntityManagerInterface $manager,
        ChannelInterface $removedChannel
    ) {
        $users = $this->userRepository->findBy(['catalogScope' => $removedChannel]);
        $channels = $this->channelRepository->findAll();

        $defaultScope = current(
            array_filter(
                $channels,
                fn($channel) => $channel->getCode() !== $removedChannel->getCode()
            )
        );

        foreach ($users as $user) {
            $user->setCatalogScope($defaultScope);
            $this->computeChangeset($uow, $manager, $user);
        }
    }

    /**
     * Update default tree of users using a tree that will be removed
     */
    protected function onTreeRemoved(UnitOfWork $uow, EntityManagerInterface $manager, CategoryInterface $removedTree)
    {
        $users = $this->userRepository->findBy(['defaultTree' => $removedTree]);
        $trees = $this->categoryRepository->getTrees();

        $defaultTree = current(
            array_filter(
                $trees,
                fn($tree) => $tree->getCode() !== $removedTree->getCode()
            )
        );

        foreach ($users as $user) {
            $user->setDefaultTree($defaultTree);
            $this->computeChangeset($uow, $manager, $user);
        }
    }

    /**
     * Update catalog locale of users using a deactivated locale
     */
    protected function onLocalesDeactivated(EntityManagerInterface $manager)
    {
        $activeLocales = $this->localeRepository->getActivatedLocales();
        $defaultLocale = current($activeLocales);

        foreach ($this->deactivatedLocales as $localeCode) {
            $deactivatedLocale = $this->localeRepository->findOneByIdentifier($localeCode);
            $users = $this->userRepository->findBy(['catalogLocale' => $deactivatedLocale]);

            foreach ($users as $user) {
                $user->setCatalogLocale($defaultLocale);
                $manager->persist($user);
            }
        }
        $this->deactivatedLocales = [];

        $manager->flush();
    }
}
