<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * UserNotification entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepository::class)]
#[ORM\Table(name: 'pim_notification_user_notification')]
class UserNotification implements UserNotificationInterface
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var bool */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected $viewed = false;

    /** @var NotificationInterface */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification::class)]
    #[ORM\JoinColumn(name: 'notification', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $notification;

    /** @var UserInterface */
    #[ORM\ManyToOne(targetEntity: \Akeneo\UserManagement\Component\Model\UserInterface::class)]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setNotification(NotificationInterface $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewed($viewed)
    {
        $this->viewed = $viewed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isViewed()
    {
        return $this->viewed;
    }
}
