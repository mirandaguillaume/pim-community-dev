<?php

namespace Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Acl\Extension\AclClassInfo;

class ActionMetadata implements AclClassInfo, \Serializable
{
    /**
     * Defines if the ACL must be enabled/disabled at creation for all roles.
     *
     * @var bool
     */
    protected $isEnabledAtCreation;

    /**
     * Gets an action name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->name;
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
     * Gets an action label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function isEnabledAtCreation(): bool
    {
        return $this->isEnabledAtCreation;
    }

    /**
     * @param string $name
     * @param string $group
     * @param string $label
     */
    public function __construct(
        protected $name = '',
        protected $group = '',
        protected $label = '',
        bool $isEnabledAtCreation = true,
        protected int $order = 0,
        /**
         * true if the ACL must be visible in the UI. eg: the edit role permissions screen
         * ACL that are not visible still exist and can be managed by the code.
         */
        protected bool $visible = true
    ) {
        $this->isEnabledAtCreation = $isEnabledAtCreation;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
                $this->name,
                $this->group,
                $this->label,
                $this->isEnabledAtCreation,
                $this->order,
                $this->visible,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        [$this->name, $this->group, $this->label, $this->isEnabledAtCreation, $this->order, $this->visible, ] = unserialize($serialized);
    }
}
