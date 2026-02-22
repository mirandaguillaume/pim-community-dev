<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Acl\Extension\AclClassInfo;

class EntitySecurityMetadata implements AclClassInfo, \Serializable
{
    /**
     * Constructor
     *
     * @param string $securityType
     * @param string $className
     * @param string $group
     * @param string $label
     * @param string[] $permissions
     */
    public function __construct(protected $securityType = '', protected $className = '', protected $group = '', protected $label = '', protected $permissions = [])
    {
    }

    /**
     * Gets the security type
     *
     * @return string
     */
    public function getSecurityType()
    {
        return $this->securityType;
    }

    /**
     * Gets an entity class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Gets a security group name
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Gets an entity label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function isEnabledAtCreation(): bool
    {
        return true;
    }

    /**
     * Gets permissions
     *
     * @return string[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Serializable interface is deprecated since PHP 8.1. Migrate to __serialize()/__unserialize() in a future PR.
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->securityType,
                $this->className,
                $this->group,
                $this->label,
                $this->permissions,
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Serializable interface is deprecated since PHP 8.1. Migrate to __serialize()/__unserialize() in a future PR.
     */
    public function unserialize($serialized): void
    {
        [
            $this->securityType,
            $this->className,
            $this->group,
            $this->label,
            $this->permissions
        ] = unserialize($serialized);
    }

    public function getOrder(): int
    {
        return 0;
    }
}
