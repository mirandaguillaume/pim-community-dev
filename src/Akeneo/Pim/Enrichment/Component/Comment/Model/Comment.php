<?php

namespace Akeneo\Pim\Enrichment\Component\Comment\Model;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * Comment model
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_comment_comment')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\Index(columns: ['resource_name', 'resource_id'], name: 'resource_name_resource_id_idx')]
#[ORM\Index(columns: ['resource_name', 'resource_uuid'], name: 'resource_name_resource_uuid_idx')]
class Comment implements CommentInterface
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var string */
    #[ORM\Column(name: 'resource_name', type: Types::STRING)]
    protected $resourceName;

    /** @var string */
    #[ORM\Column(name: 'resource_id', type: Types::STRING, length: 24, nullable: true)]
    protected $resourceId;

    #[ORM\Column(name: 'resource_uuid', type: 'uuid_binary', nullable: true)]
    protected UuidInterface $resourceUuid;

    /** @var UserInterface */
    #[ORM\ManyToOne(targetEntity: \Akeneo\UserManagement\Component\Model\UserInterface::class)]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected $author;

    /** @var string */
    #[ORM\Column(type: Types::TEXT)]
    protected $body;

    /** @var \DateTime */
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected $createdAt;

    /** @var \DateTime */
    #[ORM\Column(name: 'replied_at', type: Types::DATETIME_MUTABLE)]
    protected $repliedAt;

    /** @var CommentInterface */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface::class, inversedBy: 'children')]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $parent;

    /** @var ArrayCollection[] */
    #[ORM\OneToMany(targetEntity: \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    protected $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->repliedAt = new \DateTime('now', new \DateTimeZone('UTC'));
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
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(CommentInterface $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setRepliedAt(\DateTime $repliedAt)
    {
        $this->repliedAt = $repliedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepliedAt()
    {
        return $this->repliedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildren(ArrayCollection $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceUuid(UuidInterface $resourceUuid): void
    {
        $this->resourceUuid = $resourceUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceUuid(): ?UuidInterface
    {
        return $this->resourceUuid;
    }
}
