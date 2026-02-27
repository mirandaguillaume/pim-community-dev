<?php

namespace Akeneo\Tool\Component\Versioning\Model;

use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * Resource version entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM\VersionRepository::class)]
#[ORM\Table(name: 'pim_versioning_version')]
#[ORM\Index(columns: ['pending'], name: 'pending_idx')]
#[ORM\Index(columns: ['version'], name: 'version_idx')]
#[ORM\Index(columns: ['logged_at'], name: 'logged_at_idx')]
#[ORM\Index(columns: ['resource_name', 'resource_id', 'version'], name: 'resource_name_resource_id_version_idx')]
#[ORM\Index(columns: ['resource_name', 'resource_uuid', 'version'], name: 'resource_name_resource_uuid_version_idx')]
class Version implements VersionInterface
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /**
     * @var array
     */
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    protected $snapshot;

    /**
     * @var array
     */
    #[ORM\Column(type: Types::ARRAY)]
    protected $changeset;

    /**
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected $version;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'logged_at', type: Types::DATETIME_MUTABLE)]
    protected $loggedAt;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected $pending;

    #[ORM\Column(name: 'resource_name', type: Types::STRING)]
    protected $resourceName;

    #[ORM\Column(name: 'resource_id', type: Types::STRING, length: 24, nullable: true)]
    protected $resourceId;

    #[ORM\Column(name: 'resource_uuid', type: 'uuid_binary', nullable: true)]
    protected ?\Ramsey\Uuid\UuidInterface $resourceUuid = null;

    #[ORM\Column(type: Types::STRING)]
    protected $author;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected $context;

    /**
     * Constructor
     *
     * @param string      $resourceName
     * @param string|null $resourceId
     * @param string      $author
     * @param string|null $context
     */
    public function __construct($resourceName, $resourceId, ?\Ramsey\Uuid\UuidInterface $resourceUuid, $author, $context = null)
    {
        $this->resourceName = $resourceName;
        $this->resourceId = $resourceId;
        $this->resourceUuid = $resourceUuid;
        $this->author = $author;
        $this->context = $context;
        $this->loggedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->pending = true;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     *
     * @return Version
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getResourceUuid(): ?UuidInterface
    {
        return $this->resourceUuid;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get resource id
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Get resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * Get version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param int $version
     *
     * @return Version
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get snapshot
     *
     * @return array
     */
    public function getSnapshot()
    {
        return $this->snapshot;
    }

    /**
     * Set snapshot
     *
     *
     * @return Version
     */
    public function setSnapshot(array $snapshot)
    {
        if (!empty($snapshot)) {
            $this->pending = false;
        }

        $this->snapshot = $snapshot;

        return $this;
    }

    /**
     * Get changeset
     *
     * @return array
     */
    public function getChangeset()
    {
        return $this->changeset;
    }

    /**
     * Set changeset
     *
     *
     * @return Version
     */
    public function setChangeset(array $changeset)
    {
        $this->changeset = $changeset;

        return $this;
    }

    /**
     * Get context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \DateTime
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->pending;
    }
}
