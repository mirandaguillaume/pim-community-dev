<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Acl\Extension\AclClassInfo;

class EntitySecurityMetadata implements AclClassInfo
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

    public function __serialize(): array
    {
        return [
            $this->securityType,
            $this->className,
            $this->group,
            $this->label,
            $this->permissions,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->securityType,
            $this->className,
            $this->group,
            $this->label,
            $this->permissions
        ] = $data;
    }

    public function getOrder(): int
    {
        return 0;
    }
}
