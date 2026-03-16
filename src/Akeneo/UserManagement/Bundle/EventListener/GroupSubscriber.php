<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\UserEvents;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Aims to perform operations on user groups during creation, edition or deletion.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: UserEvents::PRE_DELETE_GROUP, method: 'preDeleteGroup')]
#[AsEventListener(event: UserEvents::PRE_UPDATE_GROUP, method: 'preUpdateGroup')]
class GroupSubscriber
{

    /**
     * Pre delete a user group
     */
    public function preDeleteGroup(GenericEvent $event)
    {
        $this->checkDefaultGroup($event);
    }

    /**
     * Pre update a user group
     */
    public function preUpdateGroup(GenericEvent $event)
    {
        $this->checkDefaultGroup($event);
    }

    /**
     * Check if the current user group is the default group.
     *
     *
     * @throws \Exception
     */
    protected function checkDefaultGroup(GenericEvent $event)
    {
        /** @var GroupInterface $group */
        $group = $event->getSubject();

        if (strtolower(User::GROUP_DEFAULT) === strtolower($group->getName())) {
            $event->stopPropagation();
            throw new \Exception(sprintf('The default group "%s" can not be updated.', $group->getName()));
        }
    }
}
