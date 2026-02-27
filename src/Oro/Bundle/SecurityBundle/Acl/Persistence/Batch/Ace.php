<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence\Batch;

use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class Ace
{
    /**
     * Constructor
     *
     * @param string                    $type
     * @param string|null               $field
     * @param bool                      $granting
     * @param int                       $mask
     * @param string|null               $strategy
     * @param bool                      $replace
     */
    public function __construct(private $type, private $field, private readonly SecurityIdentityInterface $sid, private $granting, private $mask, private $strategy, private $replace) {}

    /**
     * Gets the security identity associated with this ACE
     *
     * @return SecurityIdentityInterface
     */
    public function getSecurityIdentity()
    {
        return $this->sid;
    }

    /**
     * Gets this ACE type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the name of a field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Gets the permission mask of this ACE
     *
     * @return int
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * Indicates whether this ACE is granting, or denying
     *
     * @return bool
     */
    public function isGranting()
    {
        return $this->granting;
    }

    /**
     * Gets the strategy for comparing masks
     */
    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    /**
     * Indicates whether this ACE should replace existing ACE or not
     *
     * @return bool
     */
    public function isReplace()
    {
        return $this->replace;
    }
}
