<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Component\Model\Role;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;
use Oro\Bundle\SecurityBundle\DependencyInjection\Utils\ServiceLink;

class RoleListener
{
    protected \Oro\Bundle\SecurityBundle\DependencyInjection\Utils\ServiceLink $aclSidManagerLink;

    public function __construct(ServiceLink $aclSidManagerLink)
    {
        $this->aclSidManagerLink = $aclSidManagerLink;
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        if ($eventArgs->getObject() instanceof Role && $eventArgs->hasChangedField('role')) {
            $oldRoleName = $eventArgs->getOldValue('role');
            $newRoleName = $eventArgs->getNewValue('role');
            /** @var $aclSidManager AclSidManager */
            $aclSidManager = $this->aclSidManagerLink->getService();
            $aclSidManager->updateSid($aclSidManager->getSid($newRoleName), $oldRoleName);
        }
    }
}
