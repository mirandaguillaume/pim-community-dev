<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Notification entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_notification_notification')]
class Notification implements NotificationInterface
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected $route;

    /** @var array */
    #[ORM\Column(type: Types::ARRAY)]
    protected $routeParams = [];

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected $message;

    /** @var array */
    #[ORM\Column(type: Types::ARRAY)]
    protected $messageParams = [];

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected $comment;

    /** @var \DateTime */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected $created;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 20)]
    protected $type;

    /** @var array */
    #[ORM\Column(type: Types::ARRAY)]
    protected $context = [];

    public function __construct()
    {
        $this->created = new \DateTime('now');
    }

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
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageParams(array $messageParams)
    {
        $this->messageParams = $messageParams;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams()
    {
        return $this->messageParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }
}
