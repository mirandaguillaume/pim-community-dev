<?php

namespace Akeneo\Tool\Component\Versioning\Model;

use Ramsey\Uuid\UuidInterface;

/**
 * Resource version entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version implements VersionInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected $snapshot;

    /**
     * @var array
     */
    protected $changeset;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var \DateTime
     */
    protected $loggedAt;

    /**
     * @var bool
     */
    protected $pending;

    /**
     * Constructor
     *
     * @param string      $resourceName
     * @param string|null $resourceId
     * @param string      $author
     * @param string|null $context
     */
    public function __construct(protected $resourceName, protected $resourceId, protected ?\Ramsey\Uuid\UuidInterface $resourceUuid, protected $author, protected $context = null)
    {
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
