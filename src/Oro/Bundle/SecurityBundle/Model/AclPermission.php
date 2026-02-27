<?php

namespace Oro\Bundle\SecurityBundle\Model;

class AclPermission
{
    /**
     * Constructor
     *
     * @param string|null $name
     * @param int|null    $accessLevel Can be any AccessLevel::*_LEVEL
     */
    public function __construct(private $name = null, private $accessLevel = null) {}

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string        $name
     * @return AclPermission
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Can be any AccessLevel::*_LEVEL
     *
     * @return int
     */
    public function getAccessLevel()
    {
        return $this->accessLevel;
    }

    /**
     * @param  int           $accessLevel Can be any AccessLevel::*_LEVEL
     * @return AclPermission
     */
    public function setAccessLevel($accessLevel)
    {
        $this->accessLevel = $accessLevel;

        return $this;
    }
}
